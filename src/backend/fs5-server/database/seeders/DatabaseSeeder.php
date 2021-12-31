<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use \App\Models\Config;

class DatabaseSeeder extends Seeder
{
	private static $ranks;
	private static $divisions;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
		Config::read();

		// \App\Models\Athlete::factory()->count( 200 )->create();

		foreach( Config::$divisions as $division ) {
			$athletes = DatabaseSeeder::eligible_athletes( $division[ 'criteria' ]);
			if( count( $athletes ) == 0 ) { continue; }
			// \App\Models\Division::factory()->create( $division );
		}
    }

	/**
	 * Reads the tournament configuration
	 */
	private static function eligible_athletes( $criteria ) {
		$datemin = false;
		$datemax = false;
		Config::age_range_to_dates( $criteria[ 'age' ], $criteria, 'dob' );
		Config::rank_range_to_list( $criteria[ 'rank' ], $criteria, 'rank' );

		$query = \DB::table( 'athletes' );
		Config::apply_criteria( $query, $criteria, 'gender' );
		Config::apply_criteria( $query, $criteria, 'dob' );
		Config::apply_criteria( $query, $criteria, 'rank' );

		$sql      = str_replace( array( '?' ), array( '\'%s\'' ), $query->toSql());
		$sql      = vsprintf( $sql, $query->getBindings());
		$athletes = $query->get();
		if( count( $athletes ) > 0 ) { dump( $athletes, $sql ); }
		return $athletes;
	}

}
