<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDlRequestsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'dl_requests';

    /**
     * Run the migrations.
     * @table dl_requests
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('user_bank_account_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamp('dl_date_created')->nullable();
            $table->string('dl_customer_id', 100)->nullable();
            $table->string('dl_customer_name_entered')->nullable();
            $table->string('dl_customer_name_found')->nullable();
            $table->string('dl_request_code', 100)->nullable();
            $table->string('name_found', 100)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            $table->index(["user_bank_account_id"], 'fk_dl_requests_user_bank_accounts1_idx');

            $table->unique(["id"], 'id_UNIQUE');
            $table->nullableTimestamps();


            $table->foreign('user_bank_account_id', 'fk_dl_requests_user_bank_accounts1_idx')
                ->references('id')->on('user_bank_accounts')
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
