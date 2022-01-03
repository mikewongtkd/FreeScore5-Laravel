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
		\App\Models\Athlete::factory()->count( 200 )->create();

		foreach( Config::$divisions as $division ) {
			$athletes = DatabaseSeeder::eligible_athletes( $division );
			if( count( $athletes ) == 0 ) { continue; }

			$division = \App\Models\Division::factory()->create([
				'code'        => $division[ 'code' ],
				'description' => $division[ 'description' ],
				'criteria'    => json_encode( $division[ 'criteria' ]),
				'info'        => json_encode([ 'difficulty' => $division[ 'difficulty' ], 'headcontactrules' => $division[ 'headcontactrules' ]])
			]);

			$division = \App\Models\Division::where( 'code', '=', $division->code )->first();

			// Register each eligible athlete to the division
			foreach( $athletes as $athlete ) {
				$athlete = \App\Models\Athlete::find( $athlete->id );
				// $athlete->divisions()->save( $division );
				\App\Models\AthleteDivision::factory()->create([ 'athlete_id' => $athlete->id, 'division_id' => $division->id ]);
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
