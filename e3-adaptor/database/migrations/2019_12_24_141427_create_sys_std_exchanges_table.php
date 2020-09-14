<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdExchangesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_exchange', function (Blueprint $table) {
            $table->unsignedBigInteger('dispute_id')->comment('换货单号');
            $table->string('platform', 10)->comment('来源平台');
            $table->unsignedBigInteger('tid')->comment('交易号');
            $table->string('shop_code', 20)->comment('商店编码');
            $table->string('status', 50)->comment('状态');
            $table->string('refund_phase', 10)->nullable()->default('')->comment('申请换货阶段');
            $table->string('refund_version', 20)->nullable()->default('')->comment('版本号');
            $table->string('buyer_name', 32)->nullable()->default('')->comment('买家收货人');
            $table->string('buyer_address')->nullable()->default('')->comment('买家收货地址');
            $table->string('buyer_phone', 15)->nullable()->default('')->comment('买家联系方式');
            $table->string('buyer_logistic_name', 32)->nullable()->default('')->comment('买家发货物流公司');
            $table->string('buyer_logistic_no', 32)->nullable()->default('')->comment('买家发货物流单号');
            $table->string('seller_address')->nullable()->default('')->comment('卖家收货地址');
            $table->string('seller_logistic_name', 32)->nullable()->default('')->comment('卖家发货物流公司');
            $table->string('seller_logistic_no', 32)->nullable()->default('')->comment('卖家发货物流单号');
            $table->datetime('created')->nullable()->comment('申请时间');
            $table->datetime('modified')->nullable()->comment('平台更新时间');
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
        Schema::drop('sys_std_exchange');
    }
}
