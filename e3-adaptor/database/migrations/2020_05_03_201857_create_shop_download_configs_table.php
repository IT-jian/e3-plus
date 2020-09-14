<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShopDownloadConfigsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_download_config', function (Blueprint $table) {
            $table->integer('id', true)->comment('ID');
            $table->string('platform', 10)->comment('平台');
            $table->string('shop_code', 50)->comment('店铺');
            $table->string('code', 50)->comment('编码');
            $table->string('name', 50)->comment('名称');
            $table->unsignedTinyInteger('type')->default(1)->comment('类型：1. rds 2. api');
            $table->unsignedTinyInteger('stop_download')->default(1)->comment('停止下载');
            $table->integer('query_page_size')->comment('单次查询数量');
            $table->integer('job_page_size')->comment('单个任务批数量');
            $table->timestamp('next_query_at')->nullable()->comment('下次查询开始时间');
            $table->timestamps();

            $table->unique(['shop_code', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('shop_download_config');
    }
}
