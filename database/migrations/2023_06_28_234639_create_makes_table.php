<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMakesTable extends Migration
{
    public function up()
    {
        Schema::create('makes', function (Blueprint $table) {
            $table->increments('Make_ID');
            $table->string('Make_Name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('makes');
    }
}
