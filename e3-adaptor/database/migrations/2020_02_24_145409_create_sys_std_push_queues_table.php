<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdPushQueuesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_push_queue', function (Blueprint $table) {
            $table->bigInteger('id', true)->comment('ID');
            $table->string('bis_id', 50)->comment('业务ID：tid, refund_id 等');
            $table->string('platform', 10)->comment('平台');
            $table->string('hub', 10)->nullable()->comment('合作伙伴');
            $table->string('method', 50)->comment('类型');
            $table->unsignedTinyInteger('status')->comment('状态：0,未执行 1:执行成功 2:执行失败 3:执行中');
            $table->json('extends')->comment('扩展信息');
            $table->unsignedInteger('retry_after')->default(0)->comment('时间戳');
            $table->unsignedTinyInteger('try_times')->default(0)->comment('失败轮次');
            $table->unsignedInteger('push_version')->default(0)->comment('格式化版本');
            $table->mediumText('push_content')->default('')->comment('格式化内容');
            $table->timestamps();
            // 增加索引
            $table->index(['bis_id', 'method', 'platform'], 'index_bis_method');
            $table->index(['status', 'method'], 'index_status_method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sys_std_push_queue');
    }
}
