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
		$this->command->info( 'Loading static data tables (including configuration)' );
		\Eloquent::unguard();
		$static = "database/static/config.sql";
		\DB::unprepared( file_get_contents( $static ));

		$this->command->info( 'Reading configuration tables' );
		Config::read();

		$this->command->info( 'Seeding data tables' );

		$this->command->info( 'Seeding Athletes' );
		$athletes = \App\Models\Athlete::factory()->count( 200 )->create();
		$eligible = [];

		// Create Divisions with Eligible Athletes
		$this->command->info( 'Seeding Divisions' );
		$this->command->withProgressBar( Config::$divisions, function( $division ) use ( &$eligible ) {
			$divcode  = $division[ 'code' ];
			$athletes = DatabaseSeeder::eligible_athletes( $division );
			if( count( $athletes ) == 0 ) { return; }

			foreach( $athletes as $athlete ) {
				$aid = (string) $athlete->id;
				if( ! array_key_exists( $aid, $eligible )) { $eligible[ $aid ] = []; }
				array_push( $eligible[ $aid ], $divcode );
			}

			$division = DatabaseSeeder::create_division( $division );
		});

		// Register each eligible athlete to the division
		$this->command->newline();
		$this->command->info( 'Seeding athlete registrations' );
		foreach( $athletes as $athlete ) {
			$aid = (string) $athlete->id;

			if( ! array_key_exists( $aid, $eligible ) || count( $eligible[ $aid ]) == 0 ) {
				$this->command->error( "Error, athlete {$aid} has no eligible divisions" );

			} else if( count( $eligible[ $aid ]) == 1 ) {
				$divcode  = $eligible[ $aid ][ 0 ];
				$division = \App\Models\Division::where( 'code', '=', $divcode )->first();
				\App\Models\AthleteDivision::factory()->create([ 'athlete_id' => $aid, 'division_id' => $division->id ]);

			} else {
				$divisions = collect( array_map( function( $d ) { return \App\Models\Division::find( $d->id ); }, $eligible[ $aid ]));
				// Do something WRT grassroots and worldclass divisions

			}
		}
		$this->command->newline();
    }

	/**
	 * Create a division, given the division data
	 */
	private static function create_division( $data ) {
		$division = \App\Models\Division::factory()->create([
			'code'        => $data[ 'code' ],
			'description' => $data[ 'description' ],
			'criteria'    => json_encode( $data[ 'criteria' ]),
			'info'        => json_encode([ 'difficulty' => $data[ 'difficulty' ], 'headcontactrules' => $data[ 'headcontactrules' ]])
		]);

		$division = \App\Models\Division::where( 'code', '=', $division->code )->first();

		return $division;
	}

	/**
	 * Given a division, returns the eligible athletes for the division
	 */
	private static function eligible_athletes( $division ) {
		$criteria = $division[ 'criteria' ];
		$query = \DB::table( 'athletes' );

		// Prepare special criteria
		Config::age_range_to_dates( $criteria[ 'age' ],  $criteria, 'dob'  );
		Config::rank_range_to_list( $criteria[ 'rank' ], $criteria, 'rank' );

		// Apply all criteria
		Config::apply_criteria( $query, $criteria, 'gender' );
		Config::apply_criteria( $query, $criteria, 'dob'    );
		Config::apply_criteria( $query, $criteria, 'rank'   );

		// DatabaseSeeder::debug_sql( $query );

		// Run query
		$athletes = $query->get();
		return $athletes;
	}

	/**
	 * Prints the interpolated SQL statement for debugging purposes
	 */
	private static function debug_sql( $query ) {
		$sql = str_replace( array( '?' ), array( '\'%s\'' ), $query->toSql());
		$sql = vsprintf( $sql, $query->getBindings());
		dump( $sql );
	}

}
