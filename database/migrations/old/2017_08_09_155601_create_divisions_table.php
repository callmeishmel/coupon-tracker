<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDivisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('divisions', function (Blueprint $table) {
          $table->increments('id');
          $table->string('city_id', 255);
          $table->string('city_name', 255);
          $table->string('country', 255);
          $table->string('lat', 255);
          $table->string('lng', 1000);
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
        Schema::dropIfExists('divisions');
    }
}
