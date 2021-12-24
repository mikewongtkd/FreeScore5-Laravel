<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAthleteMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('athlete_match', function (Blueprint $table) {
            $table->uuid( 'id' )->primary();
            $table->uuid( 'athlete_id' );
            $table->uuid( 'match_id' );
            $table->uuid( 'score_id' );
			$table->enum( 'color', [ 'chung', 'hong' ] );
            $table->json( 'info' );
            $table->timestamps();
            $table->foreign( 'athlete_id' )->references( 'id' )->on( 'athlete' );
            $table->foreign( 'match_id' )->references( 'id' )->on( 'match' );
            $table->foreign( 'score_id' )->references( 'id' )->on( 'score' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('athlete_match');
    }
}
