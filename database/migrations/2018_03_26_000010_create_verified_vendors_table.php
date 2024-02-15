<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifiedVendorsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'verified_vendors';

    /**
     * Run the migrations.
     * @table verified_vendors
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('dl_request_transaction_id');
            $table->unsignedInteger('vendor_assigned_name_id');
            $table->string('name_from_transaction', 100)->nullable();
            $table->string('category_name_from_transaction', 100)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            $table->index(["vendor_assigned_name_id"], 'fk_verified_vendors_verified_vendor_assigned_names1_idx');

            $table->index(["dl_request_transaction_id"], 'fk_verified_vendors_dl_request_transactions1_idx');

            $table->unique(["id"], 'id_UNIQUE');
            $table->nullableTimestamps();


            $table->foreign('dl_request_transaction_id', 'fk_verified_vendors_dl_request_transactions1_idx')
                ->references('id')->on('dl_request_transactions')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('vendor_assigned_name_id', 'fk_verified_vendors_verified_vendor_assigned_names1_idx')
                ->references('id')->on('verified_vendor_assigned_names')
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
