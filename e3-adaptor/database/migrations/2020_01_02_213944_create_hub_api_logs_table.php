<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHubApiLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hub_api_log', function (Blueprint $table) {
            $table->bigInteger('id', true)->comment('ID');
            $table->string('api_method', 50)->nullable()->comment('请求方法');
            $table->string('request_id', 128)->nullable()->comment('请求唯一ID');
            $table->string('ip', 15)->nullable()->comment('IP');
            $table->string('partner', 10)->nullable()->comment('来源');
            $table->string('platform', 10)->nullable()->comment('平台');
            $table->text('input')->nullable()->comment('请求内容');
            $table->text('response')->nullable()->comment('响应内容');
            $table->timestamp('start_at')->nullable()->comment('请求时间');
            $table->timestamp('end_at')->nullable()->comment('响应时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hub_api_log');
    }
}
