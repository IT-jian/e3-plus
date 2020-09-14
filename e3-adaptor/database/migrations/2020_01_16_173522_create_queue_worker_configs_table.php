<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQueueWorkerConfigsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_worker_config', function (Blueprint $table) {
            $table->integer('id', true)->comment('ID');
            $table->string('code', 50)->comment('编码');
            $table->string('name', 50)->nullable()->comment('名称');
            $table->integer('process_number')->comment('并发数量');
            $table->string('command')->comment('命令');
            $table->string('user')->default('root')->comment('用户');
            $table->integer('status')->default(0)->comment('状态 0: 禁用 1: 启用');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
            $table->index('status', 'index_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('queue_worker_config');
    }
}