<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOperationLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->increments('id')->comment('日志ID');
            $table->integer('user_id')->comment('角色名称');
            $table->string('path')->comment('访问地址');
            $table->string('method', 10)->comment('访问方法');
            $table->string('ip', 15)->comment('IP');
            $table->text('input')->comment('请求内容');
            $table->text('response')->comment('响应内容');
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
        Schema::drop('operation_logs');
    }
}