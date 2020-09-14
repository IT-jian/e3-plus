<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJingdongTradesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jingdong_trade', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->comment('交易号');
            $table->string('vender_id', 50)->comment('商家编号');
            $table->string('state', 32)->comment('订单状态');
            $table->string('order_type', 32)->comment('订单类型');
            $table->unsignedBigInteger('direct_parent_order_id')->comment('直接父订单号');
            $table->unsignedBigInteger('parent_order_id')->comment('根父订单号');
            $table->datetime('created')->nullable()->comment('创建时间');
            $table->datetime('modified')->nullable()->comment('更新时间');
            $table->mediumText('origin_content')->comment('原始报文');
            $table->unsignedInteger('origin_created')->comment('报文创建时间');
            $table->unsignedInteger('origin_modified')->comment('报文更新时间');
            $table->unsignedBigInteger('version')->comment('版本');
            $table->unsignedTinyInteger('sync_status')->default(0)->comment('0:未转入,1:已转入,2:锁定中');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->primary('order_id');
            $table->index('vender_id');
            $table->index('state');
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
        Schema::drop('jingdong_trade');
    }
}
