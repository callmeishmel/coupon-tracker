<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankVerificationLogsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'bank_verification_logs';

    /**
     * Run the migrations.
     * @table bank_verification_logs
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('dl_requests_id');
            $table->unsignedInteger('dl_requests_user_bank_account_id');
            $table->tinyInteger('is_login_valid')->nullable();
            $table->tinyInteger('account_number_confidence')->nullable();
            $table->decimal('available_balance_found', 13, 2)->nullable();
            $table->decimal('current_balance_found', 13, 2)->nullable();
            $table->tinyInteger('is_account_number_match')->nullable();
            $table->tinyInteger('is_amount_verified')->nullable();
            $table->tinyInteger('is_name_match')->nullable();
            $table->tinyInteger('is_verified')->nullable();
            $table->tinyInteger('name_confidence')->nullable();
            $table->string('name_found')->nullable();
            $table->string('bank_type', 100)->nullable();
            $table->decimal('total_deposits', 13, 2)->nullable();
            $table->decimal('total_withdrawals', 13, 2)->nullable();
            $table->timestamp('transactions_from_date')->nullable();
            $table->timestamp('transactions_to_date')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            $table->index(["dl_requests_id", "dl_requests_user_bank_account_id"], 'fk_bank_verification_logs_dl_requests1_idx');
            $table->nullableTimestamps();


            $table->foreign('dl_requests_id', 'fk_bank_verification_logs_dl_requests1_idx')
                ->references('id')->on('dl_requests')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->set_schema_table);
     }
}
