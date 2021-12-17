<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'score', function (Blueprint $table) {
            $table->uuid( 'id' )->primary();
            $table->uuid( 'athlete_id' );
            $table->uuid( 'round_id' );
            $table->json( 'info' );
            $table->timestamps();
            $table->foreign( 'athlete_id' )->references( 'id' )->on( 'athlete' );
            $table->foreign( 'round_id' )->references( 'id' )->on( 'round' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'score' );
    }
}
