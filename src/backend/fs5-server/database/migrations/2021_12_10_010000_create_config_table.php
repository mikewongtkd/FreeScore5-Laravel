<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'config', function (Blueprint $table) {
            $table->uuid( 'id' )->primary();
			$table->string( 'name' );
			$table->string( 'source' );
			$table->string( 'description' );
            $table->json( 'criteria' )->nullable();
            $table->json( 'value' );
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
        Schema::dropIfExists( 'config' );
    }
}
