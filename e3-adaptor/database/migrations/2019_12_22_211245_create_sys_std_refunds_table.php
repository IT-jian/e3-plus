<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdRefundsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_refund', function (Blueprint $table) {
            $table->unsignedBigInteger('refund_id')->comment('退单号');
            $table->string('platform', 10)->comment('来源平台');
            $table->unsignedBigInteger('tid')->comment('交易号');
            $table->unsignedBigInteger('oid')->comment('子交易号');
            $table->string('shop_code', 20)->index()->comment('商店编码');
            $table->string('status', 50)->comment('退单状态');
            $table->string('order_status', 50)->nullable()->default('')->comment('订单单状态');
            $table->string('refund_phase', 10)->nullable()->default('')->comment('退单阶段');
            $table->string('refund_version', 20)->nullable()->comment('版本号');
            $table->decimal('refund_fee', 8, 2)->nullable()->default(0)->comment('退款金额');
            $table->string('company_name', 32)->nullable()->default('')->comment('退货物流公司');
            $table->string('sid', 32)->nullable()->default('')->comment('退货运单号');
            $table->unsignedTinyInteger('has_good_return')->nullable()->default(0)->comment('是否需要退货');
            $table->datetime('created')->index()->comment('下单时间');
            $table->datetime('modified')->comment('平台更新时间');
            $table->timestamp('created_at')->nullable()->comment('下载时间');
            $table->timestamp('updated_at')->nullable()->useCurrent()->comment('更新时间');

            $table->primary(['refund_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sys_std_refund');
    }
}
