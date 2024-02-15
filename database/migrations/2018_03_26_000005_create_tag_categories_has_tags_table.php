<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagCategoriesHasTagsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'tag_categories_has_tags';

    /**
     * Run the migrations.
     * @table tag_categories_has_tags
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('tag_category_id');
            $table->unsignedInteger('tag_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            $table->index(["tag_id"], 'fk_tag_categories_has_tags_tags1_idx');

            $table->index(["tag_category_id"], 'fk_tag_categories_has_tags_tag_categories1_idx');

            $table->unique(["id"], 'id_UNIQUE');
            $table->nullableTimestamps();


            $table->foreign('tag_category_id', 'fk_tag_categories_has_tags_tag_categories1_idx')
                ->references('id')->on('tag_categories')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('tag_id', 'fk_tag_categories_has_tags_tags1_idx')
                ->references('id')->on('tags')
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
