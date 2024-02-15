<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_info', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('user_id')->nullable();
          $table->string('first_name', 255);
          $table->string('last_name', 255);
          $table->timestamps();
          $table->integer('zip_code')->nullable();
          $table->string('radius', 255)->nullable();

          $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_info');
    }
}
