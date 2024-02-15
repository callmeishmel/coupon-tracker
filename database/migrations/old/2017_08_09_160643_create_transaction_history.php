<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userId')->nullable();
            $table->string('transactionDate', 255)->nullable();
            $table->string('amount', 255)->nullable();
            $table->string('running_balance', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('type_codes', 255)->nullable();
            $table->string('is_refresh', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->string('category', 1000)->nullable();
            $table->timestamps();

            $table->index('userId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_history');
    }
}
