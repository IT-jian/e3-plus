<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJingdongStepTradesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jingdong_step_trade', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->comment('平台ID');
            $table->unsignedBigInteger('order_id')->comment('交易号');
            $table->unsignedBigInteger('presale_id')->comment('预售ID');
            $table->string('shop_id', 50)->comment('商家编号');
            $table->string('order_status', 32)->comment('订单状态');
            $table->string('order_type', 32)->comment('订单类型');
            $table->datetime('create_time')->comment('创建时间');
            $table->datetime('update_time')->comment('更新时间');
            $table->mediumText('origin_content')->comment('原始报文');
            $table->unsignedInteger('origin_created')->comment('报文创建时间');
            $table->unsignedInteger('origin_modified')->comment('报文更新时间');
            $table->unsignedTinyInteger('sync_status')->default(0)->comment('0:未转入,1:已转入,2:锁定中');
            $table->unsignedBigInteger('version')->comment('版本');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->primary('id');
            $table->index('shop_id');
            $table->index('order_status');
            $table->index('origin_modified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('jingdong_step_trade');
    }
}
