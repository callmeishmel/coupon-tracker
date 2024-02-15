<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifiedVendorsHasTagsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'verified_vendors_has_tags';

    /**
     * Run the migrations.
     * @table verified_vendors_has_tags
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('tag_id');
            $table->unsignedInteger('vendor_assigned_name_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            $table->index(["vendor_assigned_name_id"], 'fk_verified_vendors_has_tags_verified_vendor_assigned_names_idx');

            $table->index(["tag_id"], 'fk_verified_vendors_has_tags_tags1_idx');

            $table->unique(["id"], 'id_UNIQUE');
            $table->nullableTimestamps();


            $table->foreign('tag_id', 'fk_verified_vendors_has_tags_tags1_idx')
                ->references('id')->on('tags')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('vendor_assigned_name_id', 'fk_verified_vendors_has_tags_verified_vendor_assigned_names_idx')
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
