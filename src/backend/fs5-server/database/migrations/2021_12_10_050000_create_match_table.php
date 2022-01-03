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
        Schema::create( 'matches', function (Blueprint $table) {
            $table->uuid( 'id' )->primary();
            $table->uuid( 'division_id' );
            $table->string( 'number' );
            $table->enum( 'round', [ 'f', 'sf', 'qf', 'ro16', 'ro32', 'ro64', 'ro128', 'ro256' ] );
            $table->json( 'info' )->nullable();
            $table->timestamps();
			$table->foreign( 'division_id' )->references( 'id' )->on( 'divisions' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'matches' );
    }
}
