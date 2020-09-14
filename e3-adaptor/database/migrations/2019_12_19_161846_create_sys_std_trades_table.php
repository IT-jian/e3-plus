<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdTradesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_trade', function (Blueprint $table) {
            $table->unsignedBigInteger('tid')->comment('平台交易号');
            $table->string('platform', 10)->comment('来源平台');
            $table->string('shop_code', 20)->index()->comment('商家编码');
            $table->decimal('total_fee', 10, 2)->comment('商品金额');
            $table->decimal('discount_fee', 10, 2)->comment('优惠金额');
            $table->decimal('coupon_fee', 10, 2)->default('0')->comment('使用红包付款的金额');
            $table->string('pay_no', 255)->nullable()->comment('支付号');
            $table->decimal('payment', 10, 2)->comment('支付金额');
            $table->string('pay_type', 32)->nullable()->default('')->comment('支付类型');
            $table->string('pay_status', 20)->nullable()->default('')->comment('支付状态');
            $table->decimal('post_fee', 10, 2)->nullable()->default(0.00)->comment('邮费');
            $table->string('receiver_name', 255)->comment('收件人');
            $table->string('receiver_country', 20)->nullable()->default('')->comment('收件人国家');
            $table->string('receiver_state', 50)->comment('收件人省份');
            $table->string('receiver_city', 50)->nullable()->default('')->comment('收件人市');
            $table->string('receiver_district', 50)->nullable()->default('')->comment('收件人区');
            $table->string('receiver_town', 50)->nullable()->default('')->comment('收件人街道');
            $table->string('receiver_address', 255)->comment('收件人详细地址');
            $table->string('receiver_zip', 10)->nullable()->default('')->comment('收件人邮编');
            $table->string('receiver_mobile', 255)->nullable()->default('')->comment('手机');
            $table->string('receiver_phone', 255)->nullable()->default('')->comment('收件人电话');
            $table->string('buyer_email', 255)->nullable()->default('')->comment('买家邮箱');
            $table->string('status', 50)->comment('交易状态');
            $table->string('type', 20)->nullable()->default('fixed')->comment('交易类型');
            $table->string('buyer_nick', 255)->nullable()->default('')->comment('买家昵称');
            $table->tinyInteger('seller_flag')->nullable()->default(0)->comment('卖家备注旗帜');
            $table->string('seller_memo', 255)->nullable()->default('')->comment('卖家留言');
            $table->string('buyer_message', 255)->nullable()->default('')->comment('买家留言');
            $table->string('step_trade_status', 32)->nullable()->default('')->comment('分阶段付款状态');
            $table->decimal('step_paid_fee', 10, 2)->nullable()->default(0.00)->comment('分阶段付款金额');
            $table->string('pay_time', 50)->nullable()->comment('支付时间');
            $table->datetime('created')->index()->comment('下单时间');
            $table->datetime('modified')->comment('平台更新时间');
            $table->timestamp('created_at')->nullable()->useCurrent()->comment('下载时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');
            // 联合主键
            $table->primary(['tid','platform']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sys_std_trade');
    }
}
