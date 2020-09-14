<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJingdongRefundAppliesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jingdong_refund_apply', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary()->comment('退款Id');
            $table->string('vender_id', 50)->comment('商家编号');
            $table->string('order_id', 50)->comment('交易号');
            $table->string('status', 32)->comment('状态');
            $table->string('reason', 255)->nullable()->default('')->comment('退款原因');
            $table->timestamp('apply_time')->nullable()->comment('申请时间');
            $table->timestamp('check_time')->nullable()->comment('审核时间');
            $table->mediumText('origin_content')->comment('原始报文');
            $table->unsignedInteger('origin_created')->comment('报文创建时间');
            $table->unsignedInteger('origin_modified')->comment('报文更新时间');
            $table->unsignedTinyInteger('sync_status')->default(0)->nullable()->comment('转入状态');

            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('jingdong_refund_apply');
    }
}
