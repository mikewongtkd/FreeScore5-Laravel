<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAthleteRegistrantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('athlete_registrants', function (Blueprint $table) {
            $table->uuid( 'id' );
            $table->uuid( 'registrants_id' );
            $table->uuid( 'athlete_id' );
            $table->uuid( 'info' );
            $table->timestamps();
            $table->foreign( 'registrants_id' )->references( 'id' )->on( 'registrants' );
            $table->foreign( 'athlete_id' )->references( 'id' )->on( 'athlete' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('athlete_registrants');
    }
}
