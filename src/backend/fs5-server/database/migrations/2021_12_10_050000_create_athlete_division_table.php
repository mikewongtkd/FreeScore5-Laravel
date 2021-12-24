<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAthleteDivisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('athlete_division', function (Blueprint $table) {
            $table->uuid( 'id' )->primary();
            $table->uuid( 'athlete_id' );
            $table->uuid( 'division_id' );
			$table->float( 'seeding' );
			$table->integer( 'seed_rank' );
            $table->json( 'info' );
            $table->timestamps();
            $table->foreign( 'athlete_id' )->references( 'id' )->on( 'athlete' );
            $table->foreign( 'division_id' )->references( 'id' )->on( 'division' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('athlete_division');
    }
}
