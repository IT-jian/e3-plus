<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJingdongRefundsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jingdong_refund', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->comment('退单Id');
            $table->string('vender_id', 50)->comment('商家编号');
            $table->string('order_id', 50)->comment('交易号');
            $table->string('service_status', 32)->comment('状态');
            $table->string('customer_expect', 32)->comment('客户期望');
            $table->string('change_sku', 32)->default(0)->nullable()->comment('换新skuId');
            $table->unsignedBigInteger('apply_time')->comment('申请时间，京东为毫秒时间戳');
            $table->unsignedBigInteger('update_date')->comment('最后修改时间，京东为毫秒时间戳');
            $table->mediumText('origin_content')->comment('原始报文');
            $table->unsignedInteger('origin_created')->comment('报文创建时间');
            $table->unsignedInteger('origin_modified')->comment('报文更新时间');
            $table->unsignedTinyInteger('sync_status')->default(0)->nullable()->comment('转入状态');
            $table->unsignedBigInteger('sys_version')->comment('版本');

            $table->primary('service_id');
            $table->index('vender_id');
            $table->index('order_id');
            $table->index('service_status');
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
        Schema::drop('jingdong_refund');
    }
}