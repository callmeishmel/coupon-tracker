<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifiedVendorAssignedNamesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'verified_vendor_assigned_names';

    /**
     * Run the migrations.
     * @table verified_vendor_assigned_names
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('assigned_name');
            $table->unsignedTinyInteger('is_tagged')->nullable()->default('0');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');

            $table->unique(["id"], 'id_UNIQUE');
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
       Schema::dropIfExists($this->set_schema_table);
     }
}
