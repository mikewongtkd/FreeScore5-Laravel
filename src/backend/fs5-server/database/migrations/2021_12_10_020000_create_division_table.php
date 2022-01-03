<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDivisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'divisions', function (Blueprint $table) {
            $table->uuid( 'id' )->primary();
			$table->string( 'code' );
			$table->string( 'description' );
			$table->json( 'criteria' );
            $table->json( 'info' )->nullable();
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
        Schema::dropIfExists( 'divisions' );
    }
}
