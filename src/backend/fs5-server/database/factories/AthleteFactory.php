<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AthleteFactory extends Factory
{
	protected $model = \App\Models\Athlete::class;
	protected static $growth_table = null;

	/**
	 * Randomly generates a date of birth using a skewed distribution
	 *
	 * @return Date of Birth (dob) in the format 'Y-m-d' (e.g. '1981-11-05')
	 */
	// ============================================================
	private static function dob() {
	// ============================================================
		$days  = rand( 1, 364 );
		$dob   = date_sub( new \DateTime(), date_interval_create_from_date_string( "{$days} days" ));
		$rolls = [];
		foreach( range( 1, 6 ) as $i ) { $rolls[]= rand( 1, 10 ); } # 6d10+2, take lowest 3
		sort( $rolls );
		array_splice( $rolls, 3 );
		$sum   = array_sum( $rolls ) + 2;
		date_sub( $dob, date_interval_create_from_date_string( "{$sum} years" ));
		return date_format( $dob, 'Y-m-d' );
	}

	/**
	 * Randomly generates a gender using a fair coin distribution
	 *
	 * @return a gender string [female|male]
	 */
	// ============================================================
	private static function gender() {
	// ============================================================
		if( rand( 1, 2 ) == 1 ) { return 'female'; } else { return 'male'; }
	}

	/**
	 * Converts a DOB to age in months
	 *
	 * @return a whole number indicating age in months
	 */
	// ============================================================
	private static function age_months( $dob ) {
	// ============================================================
		$dob = new \DateTime( $dob );
		return ((2021 - intval( date_format( $dob, 'Y' ))) * 12) + (12 - intval( date_format( $dob, 'm' )));
	}

	/**
	 * Randomly generates a value from -infinity to infinity, following a standard distribution
	 *
	 * See https://en.wikipedia.org/wiki/Box%E2%80%93Muller_transform
	 *
	 * @return a float value
	 */
	// ============================================================
	private static function zrand(){
	// ============================================================
		$x = mt_rand()/mt_getrandmax();
		$y = mt_rand()/mt_getrandmax();
		return sqrt(-2*log($x))*cos(2*pi()*$y);
	}

	/**
	 * Approximation by linear interpolation for human growth between two Z-scores
	 *
	 * @return a float value
	 */
	// ============================================================
	private static function lin_approx( $z, $chartline ) {
	// ============================================================
		$floor = floor( $z );
		$ceil  = ceil( $z );
		$rem   = $z - $floor;
		if( $floor < -4 ) { $floor = -4; $ceil = -4; } // The lowest both charts go are -4 SD (height goes from -5 SD to 4 SD)
		if( $ceil  >  4 ) { $floor =  4; $ceil =  4; } // The highest both charts go are +4 SD

		if( $floor == $ceil ) { return $chartline[ $floor ]; }

		$min    = $chartline[ $floor ];
		$max    = $chartline[ $ceil ];
		$range  = $max - $min;
		$approx = $min + ($range * $rem);
		$approx = floatval( sprintf( "%.1f", $approx ));

		return $approx;
	}

	/**
	 * Given a DOB, gender, and growth (a Z-score), generates a height based on
	 * WHO 2007 data. Needed to calculate weight from BMI.
	 *
	 * @returns a float value corresponding to height (cm)
	 */
	// ============================================================
	private static function height( $dob, $gender, $growth ) {
	// ============================================================
		if( AthleteFactory::$growth_table === null ) { AthleteFactory::read_who2007_growth_tables(); }
		$months = AthleteFactory::age_months( $dob );
		if( $months < 61  ) { $months = 61;  } // The lowest both charts go is 61 months (5 years)
		if( $months > 228 ) { $months = 228; } // The highest both charts go is 228 months (19 years)
		$chartline = AthleteFactory::$growth_table[ 'hfa' ][ $gender ][ $months ];
		$height    = AthleteFactory::lin_approx( $growth, $chartline );
		return $height;
	}

	/**
	 * Given a DOB, gender, and growth (a Z-score), generates a weight based on
	 * WHO 2007 data
	 *
	 * @returns a float value corresponding to weight (kg)
	 */
	// ============================================================
	private static function weight( $dob, $gender, $growth ) {
	// ============================================================
		if( AthleteFactory::$growth_table === null ) { AthleteFactory::read_who2007_growth_tables(); }
		$months = AthleteFactory::age_months( $dob );
		if( $months < 61  ) { $months = 61;  } // The lowest both charts go is 61 months (5 years)
		if( $months > 228 ) { $months = 228; } // The highest both charts go is 228 months (19 years)
		$chartline = AthleteFactory::$growth_table[ 'bmi' ][ $gender ][ $months ];
		$bmi       = AthleteFactory::lin_approx( $growth, $chartline );

		$height = AthleteFactory::height( $dob, $gender, $growth );
		$weight = $bmi * (($height/100) ** 2);
		return floatval( sprintf( "%.1f", $weight ));
	}

	/**
	 * Reads the WHO 2007 growth curves (available on WHO website as Excel
	 * *.xlsx files, converted to CSV for accessibility)
	 * https://www.who.int/tools/growth-reference-data-for-5to19-years
	 */
	// ============================================================
	private static function read_who2007_growth_tables() {
	// ============================================================
		$table = \DB::table( 'config' )->where( 'criteria->key', '=', 'growth_curves' )->pluck( 'value' );
		AthleteFactory::$growth_table = $table = json_decode( $table[ 0 ], true );
		return $table;
	}

	/**
	 * Returns a belt rank
	 */
	// ============================================================
	private static function rank() {
	// ============================================================
		/* MW TODO Read DB for tournament.config.belts and use that, if available */
		$colors = [ 
			'default'    => [ 'yellow' => 1, 'green' => 1, 'blue' => 1, 'red' => 1, 'black' => 2 ],
			'usatkd'     => [ 'yellow' => 1, 'green' => 1, 'blue' => 1, 'red' => 1, 'black' => 2 ],
			'cuta-local' => [ 'yellow' => 8, 'green' => 8, 'blue' => 8, 'red' => 8, 'black1' => 8, 'black2' => 7, 'black3' => 5, 'black4' => 3, 'black5' => 2, 'black6' => 1, 'black7' => 1, 'black8' => 1 ]
		];

		$color  = $colors[ 'default' ];
		$n      = array_sum( array_values( $color ));
		$choice = rand( 1, $n );
		$rank   = null;

		foreach( $color as $current => $weight ) {
			if( $choice > $weight ) { $choice -= $weight; } else { $rank = $current; break; }
		}

		return $rank;
	}

    /**
     * Define the model's default state.
     *
     * @return array
     */
	// ============================================================
    public function definition()
	// ============================================================
    {
		$name   = explode( ' ', $this->faker->unique()->name());
		if( preg_match( '/(?:Dr\.|Miss|Mr\.|Mrs\.|Ms\.|Prof\.)/', $name[ 0 ])) { array_shift( $name ); }  // Discard titles
		if( preg_match( '/(?:DDS|DVM|I|II|III|IV|IX|Jr\.|MD|PhD|Sr\.|V|VI|VII|VIII|X)/', $name[ count( $name )-1 ])) { array_pop( $name ); } // Discard titles
		$fname  = array_shift( $name );
		$lname  = implode( ' ', $name );
		$dob    = AthleteFactory::dob();
		$gender = AthleteFactory::gender();
		$growth = AthleteFactory::zrand(); 
		$weight = AthleteFactory::weight( $dob, $gender, $growth );
		$rank   = AthleteFactory::rank();

		$entry = [
			'id'     => Str::uuid(),
			'fname'  => $fname,
			'lname'  => $lname,
			'noc'    => strtolower( $this->faker->countryISOAlpha3()),
			'email'  => $this->faker->email(),
			'dob'    => $dob,
			'weight' => $weight,
			'gender' => $gender,
			'rank'   => $rank,
			'info'   => null
		];

		return $entry;
    }
}
