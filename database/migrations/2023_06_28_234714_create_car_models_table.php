<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarModelsTable extends Migration
{
    public function up()
    {
        Schema::create('car_models', function (Blueprint $table) {
            $table->increments('Model_ID');
            $table->integer('Make_ID')->unsigned();
            $table->string('Model_Name');
            $table->foreign('Make_ID')->references('Make_ID')->on('makes');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_models');
    }
}
