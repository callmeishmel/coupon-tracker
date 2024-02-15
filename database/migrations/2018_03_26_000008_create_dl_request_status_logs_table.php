<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDlRequestStatusLogsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'dl_request_status_logs';

    /**
     * Run the migrations.
     * @table dl_request_status_logs
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('dl_request_id');
            $table->unsignedInteger('bank_account_id');
            $table->tinyInteger('request_status')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            $table->index(["dl_request_id", "bank_account_id"], 'fk_dl_request_status_logs_dl_requests1_idx');

            $table->unique(["id"], 'id_UNIQUE');
            $table->nullableTimestamps();


            $table->foreign('dl_request_id', 'fk_dl_request_status_logs_dl_requests1_idx')
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
