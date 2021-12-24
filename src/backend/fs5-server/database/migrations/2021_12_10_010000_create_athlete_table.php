<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAthleteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'athlete', function (Blueprint $table) {
            $table->uuid( 'id' )->primary();
			$table->string( 'fname' );
			$table->string( 'lname' );
			$table->string( 'noc' )->nullable();
			$table->string( 'email' );
			$table->datetime( 'dob' );
			$table->float( 'weight' )->nullable();
			$table->enum( 'gender', [ 'male', 'female', 'mixed' ] )->nullable();
			$table->string( 'rank' );
            $table->json( 'info' );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'athlete' );
    }
}
