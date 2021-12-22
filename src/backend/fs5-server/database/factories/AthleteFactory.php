<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AthleteFactory extends Factory
{
	private const growth_table = read_who2007_growth_tables();

	/**
	 * Randomly generates a date of birth using a skewed distribution
	 *
	 * @return Date of Birth (dob) in the format 'Y-m-d' (e.g. '1981-11-05')
	 */
	private static function dob() {
		$dob = $this->faker->dateTimeBetween( '-1 year', 'now' );
		$rolls = [];
		foreach( range( 1, 6 ) as $i ) { $rolls[]= rand( 1, 8 ); } # Roll 6d8, drop highest 3
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
	private static function gender() {
		if( rand( 1, 2 ) == 1 ) { return 'female'; } else { return 'male'; }
	}

	/**
	 * Converts a DOB to age in months
	 *
	 * @return a whole number indicating age in months
	 */
	private static function age_months( $dob ) {
		$dob = new DateTime( $dob );
		return ((2021 - intval( date_format( $dob, 'Y' ))) * 12) + (12 - intval( date_format( $dob, 'm' )));
	}

	/**
	 * Randomly generates a value from -infinity to infinity, following a standard distribution
	 *
	 * See https://en.wikipedia.org/wiki/Box%E2%80%93Muller_transform
	 *
	 * @return a float value
	 */
	private static function zrand(){
		$x = mt_rand()/mt_getrandmax();
		$y = mt_rand()/mt_getrandmax();
		return sqrt(-2*log($x))*cos(2*pi()*$y);
	}

	/**
	 * Approximation by linear interpolation for human growth between two Z-scores
	 *
	 * @return a float value
	 */
	private static function lin_approx( $z, $chartline ) {
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
	 * Given a DOB, gender, and growth (a Z-score), generates a height based on WHO 2007 data
	 *
	 * @returns a float value corresponding to height (cm)
	 */
	private static function height( $dob, $gender, $growth ) {
		$months = age_months( $dob );
		if( $months < 61  ) { $months = 61;  } // The lowest both charts go is 61 months (5 years)
		if( $months > 228 ) { $months = 228; } // The highest both charts go is 228 months (19 years)
		$chartline = $this->growth_table[ 'hfa' ][ $gender ][ $months ];
		$height    = lin_approx( $growth, $chartline );
		return $height;
	}

	/**
	 * Given a DOB, gender, and growth (a Z-score), generates a weight based on WHO 2007 data
	 *
	 * @returns a float value corresponding to weight (kg)
	 */
	private static function weight( $dob, $gender, $growth ) {
		$months = age_months( $dob );
		if( $months < 61  ) { $months = 61;  } // The lowest both charts go is 61 months (5 years)
		if( $months > 228 ) { $months = 228; } // The highest both charts go is 228 months (19 years)
		$chartline = $this->growth_table[ 'bmi' ][ $gender ][ $months ];
		$bmi       = lin_approx( $growth, $chartline );

		$height = height( $dob, $gender, $growth );
		$weight = $bmi * (($height/100) ** 2);
		return floatval( sprintf( "%.1f", $weight ));
	}

	/**
	 * Reads the WHO 2007 growth curves
	 */
	private static function read_who2007_growth_tables() {
		$table = [];
		$gmap  = [ 'female' => 'girls', 'male' => 'boys' ];
		foreach( [ 'bmi', 'hfa' ] as $measure ) {
			$table[ $measure ] = [];
			foreach( [ 'female', 'male' ] as $gender ) {
				$table[ $measure ][ $gender ] = [];
				$g      = $gmap[ $gender ];
				$file   = __DIR__ . "/data/{$measure}-{$g}-z-who-2007-exp.csv";
				$fh     = fopen( $file, 'r' ) or die( "Can't open '$file'" );
				$header = fgetcsv( $fh );
				while( $row = fgetcsv( $fh )) {
					$entry = array_combine( $header, $row );
					$month = null;

					foreach( array_keys( $entry ) as $key ) { 

						if( preg_match( '/month/i', $key )) { // There's a hidden non-printable, non-ASCII character somewhere; use regex to match
							$month = intval( $entry[ $key ]);
							unset( $entry[ $key ]);

						} elseif( preg_match( '/SD(\d+)(?:neg)?/', $key, $match )) {
							$z = intval( $match[ 1 ]);
							if( preg_match( '/neg/', $key )) { $z = -$z; }
							$entry[ $z ] = floatval( $entry[ $key ]);
							unset( $entry[ $key ]);

						} else {
							$entry[ $key ] = floatval( $entry[ $key ]); 
						}
					}
					$table[ $measure ][ $gender ][ $month ] = $entry;
				}
				fclose( $fh );
			}
		}
		return $table;
	}

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
		$name   = explode( ' ', $this->faker->unique()->name());
		$fname  = array_shift( $name );
		$lname  = implode( ' ', $name );
		$dob    = dob();
		$gender = gender();
		$growth = zrand(); 
		$weight = weight( $dob, $gender );

		$entry = [
			'fname'  => $fname,
			'lname'  => $lname,
			'noc'    => strtolower( $this->faker->countryISOAlpha3()),
			'email'  => $this->faker->email(),
			'dob'    => dob(),
			'weight' => weight
			'gender' =>
			'rank'   =>
		];

		$entry[ 'info' ] = [];
		$entry[ 'info' ][ 'registration' ] = json_encode( $entry );

    }
}
