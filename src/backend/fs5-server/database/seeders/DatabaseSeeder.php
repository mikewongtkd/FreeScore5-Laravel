<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Models\Config;
use App\Models\Athlete;
use App\Models\AthleteDivision;
use App\Models\Division;

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
		$athletes = Athlete::factory()->count( 1000 )->create();
		$eligible = [];
		$lookup   = [];

		// Create Divisions with Eligible Athletes
		$this->command->info( 'Seeding Divisions' );
		$this->command->withProgressBar( Config::$divisions, function( $division ) use ( &$eligible, &$lookup ) {
			$divcode            = $division[ 'code' ];
			$lookup[ $divcode ] = $division;
			$athletes           = DatabaseSeeder::eligible_athletes( $division );
			if( count( $athletes ) == 0 ) { return; }

			foreach( $athletes as $athlete ) {
				$aid = (string) $athlete->id;
				if( ! array_key_exists( $aid, $eligible )) { $eligible[ $aid ] = []; }
				array_push( $eligible[ $aid ], $divcode );
			}
		});

		// Register each eligible athlete to the division
		$this->command->newline();
		$this->command->info( 'Seeding athlete registrations' );
		$this->command->withProgressBar( $athletes, function( $athlete ) use ( $eligible, $lookup ) {
			$aid = (string) $athlete->id;

			if( ! array_key_exists( $aid, $eligible ) || count( $eligible[ $aid ]) == 0 ) {
				$this->command->error( "Error, athlete {$aid} has no eligible divisions" );

			} else if( count( $eligible[ $aid ]) == 1 ) {
				$divcode  = $eligible[ $aid ][ 0 ];
				$division = DatabaseSeeder::create_division( $lookup[ $divcode ], $divcode );
				AthleteDivision::factory()->create([ 'athlete_id' => $aid, 'division_id' => $division->id ]);

			} else {
				$divisions    = array_map( function( $d ) use ($lookup) { return $lookup[ $d ]; }, $eligible[ $aid ]);
				$difficulties = array_map( function( $x ) { return $x[ 'difficulty' ]; }, $divisions );

				// Solve this with a function that does pairwise combinatorics of all eligible divisions
				// that calls another function that does double dispatch. The double dispatch then manages
				// a list of all other interactions (i.e. up to n types of disciplines in the double dispatch table)
				if( in_array( 'Grassroots', $difficulties ) && in_array( 'Worldclass', $difficulties )) {
					$rand = rand() / mt_getrandmax();
					// 50% remove Worldclass
					if( $rand < 0.5 ) {
						$divisions = array_filter( $divisions, function( $x ) { return $x[ 'difficulty' ] != 'Worldclass'; });

					// 40% remove Grassroots
					} else if( $rand < 0.9 ) {
						$divisions = array_filter( $divisions, function( $x ) { return $x[ 'difficulty' ] != 'Grassroots'; });
					}
				}
				foreach( $divisions as $division ) {
					$division = DatabaseSeeder::create_division( $division, $division[ 'code' ]);

					AthleteDivision::factory()->create([ 'athlete_id' => $aid, 'division_id' => $division->id ]);
				}
			}
		});
		$this->command->newline();
    }

	/**
	 * Create a division, given the division data
	 */
	private static function create_division( $data, $divcode = null ) {
		if( $divcode ) {
			$exists   = Division::where( 'code', '=', $divcode )->first();
			if( $exists ) { return $exists; }
		}
		$division = Division::factory()->create([
			'code'        => $data[ 'code' ],
			'description' => $data[ 'description' ],
			'criteria'    => json_encode( $data[ 'criteria' ]),
			'info'        => json_encode([ 'difficulty' => $data[ 'difficulty' ], 'headcontactrules' => $data[ 'headcontactrules' ]])
		]);

		$division = Division::where( 'code', '=', $division->code )->first();

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
		Config::apply_criteria( $query, $criteria, 'weight' );

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
