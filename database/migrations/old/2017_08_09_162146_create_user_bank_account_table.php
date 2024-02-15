<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBankAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bank_account', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userId')->nullable();
            $table->string('bank_name', 255);
            $table->timestamps();
            $table->string('account_number', 255);
            $table->string('security_question', 1000)->nullable();
            $table->string('password', 1000)->nullable();
            $table->string('account_nick_name', 255);
            $table->string('routing_number', 255);
            $table->string('request_code', 255)->nullable();
            $table->string('is_login_valid', 255)->nullable();
            $table->string('is_verified', 255)->nullable();
            $table->string('is_completed', 255)->nullable();
            $table->string('first_time_transaction', 255)->nullable()->default('0');

            $table->index('userId');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_bank_account');
    }
}
