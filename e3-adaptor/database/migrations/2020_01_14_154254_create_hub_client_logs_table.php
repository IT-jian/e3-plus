<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHubClientLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hub_client_log', function (Blueprint $table) {
            $table->bigInteger('id', true)->comment('ID');
            $table->string('api_method', 50)->nullable()->comment('请求方法');
            $table->string('class_name', 32)->nullable()->comment('请求类名');
            $table->string('keyword', 64)->nullable()->comment('关键词，业务编码等');
            $table->string('app_name', 32)->nullable()->comment('请求来源');
            $table->string('url', 255)->nullable()->comment('请求地址');
            $table->text('input')->nullable()->comment('请求内容');
            $table->text('response')->nullable()->comment('响应内容');
            $table->string('status_code', 10)->nullable()->comment('响应状态码');
            $table->string('message', 255)->nullable()->comment('信息');
            $table->timestamp('start_at')->nullable()->comment('请求时间');
            $table->timestamp('end_at')->nullable()->comment('响应时间');
            $table->index(['keyword', 'class_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hub_client_log');
    }
}
