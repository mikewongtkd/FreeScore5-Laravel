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
			$athletes = DatabaseSeeder::eligible_athletes( $division );
			if( count( $athletes ) == 0 ) { continue; }

			\App\Models\Division::factory()->create([
				'code' => $division[ 'code' ],
				'description' => $division[ 'description' ],
				'criteria' => json_encode( $division[ 'criteria' ]),
				'info' => json_encode([ 'difficulty' => $division[ 'difficulty' ], 'headcontactrules' => $division[ 'headcontactrules' ]])
			]);

			if( preg_match( '/grassroots/i', $division[ 'difficulty' ])) {
			}
		}
    }

	/**
	 * Reads the tournament configuration
	 */
	private static function eligible_athletes( $division ) {
		$criteria = $division[ 'criteria' ];
		$query = \DB::table( 'athletes' );

		// Prepare special criteria
		Config::age_range_to_dates( $criteria[ 'age' ], $criteria, 'dob' );
		Config::rank_range_to_list( $criteria[ 'rank' ], $criteria, 'rank' );

		// Apply all criteria
		Config::apply_criteria( $query, $criteria, 'gender' );
		Config::apply_criteria( $query, $criteria, 'dob' );
		Config::apply_criteria( $query, $criteria, 'rank' );

		$debug_sql = false;
		if( $debug_sql ) {
			$sql = str_replace( array( '?' ), array( '\'%s\'' ), $query->toSql());
			$sql = vsprintf( $sql, $query->getBindings());
			dump( $sql );
		}

		// Run query
		$athletes = $query->get();
		return $athletes;
	}

}
