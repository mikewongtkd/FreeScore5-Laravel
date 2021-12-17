<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'match', function (Blueprint $table) {
            $table->uuid( 'id' )->primary();
            $table->string( 'number' );
            $table->uuid( 'round_id' );
            $table->uuid( 'chung' );
            $table->uuid( 'hong' );
            $table->json( 'info' );
            $table->timestamps();
            $table->foreign( 'round_id' )->references( 'id' )->on( 'round' );
            $table->foreign( 'chung' )->references( 'id' )->on( 'athlete' );
            $table->foreign( 'hong' )->references( 'id' )->on( 'athlete' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'match' );
    }
}
