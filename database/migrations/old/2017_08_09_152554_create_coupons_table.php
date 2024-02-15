<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('groupon_uid', 255);
            $table->string('deal_url', 1000);
            $table->string('title', 255);
            $table->string('announcement_title', 1000);
            $table->string('short_announcement_title', 1000);
            $table->string('high_light_html', 1000);
            $table->string('fine_print', 1000);
            $table->string('medium_image_url', 1000);
            $table->string('large_image_url', 1000);
            $table->string('status', 10);
            $table->string('merchant', 2000);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('category', 255);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE coupons ADD pitch_html LONGBLOB");
        DB::statement("ALTER TABLE coupons ADD options LONGBLOB");
        DB::statement("ALTER TABLE coupons ADD groupon_api_dump LONGBLOB");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
