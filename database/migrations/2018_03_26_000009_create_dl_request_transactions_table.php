<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDlRequestTransactionsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'dl_request_transactions';

    /**
     * Run the migrations.
     * @table dl_request_transactions
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
            $table->decimal('amount_spent', 13, 2)->nullable();
            $table->timestamp('purchase_date')->nullable();
            $table->string('transaction_codes', 45)->nullable();
            $table->mediumText('description')->nullable();
            $table->mediumText('filtered_vendor_name')->nullable();
            $table->string('dl_category', 100)->nullable();
            $table->string('dl_category_vendor', 100)->nullable();
            $table->decimal('balance_at_time_of_purchase', 13, 2)->nullable();
            $table->tinyInteger('is_ignored')->default('0');
            $table->tinyInteger('vendor_verified')->default('0');
            $table->unsignedInteger('verified_vendor_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            $table->index(["dl_request_id"], 'fk_dl_request_transactions_dl_requests_idx');

            $table->unique(["id"], 'id_UNIQUE');
            $table->nullableTimestamps();


            $table->foreign('dl_request_id', 'fk_dl_request_transactions_dl_requests_idx')
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
