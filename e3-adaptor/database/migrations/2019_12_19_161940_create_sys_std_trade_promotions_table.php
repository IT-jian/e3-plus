<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdTradePromotionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_trade_promotion', function (Blueprint $table) {
            $table->unsignedBigInteger('tid')->comment('交易号');
            $table->string('platform', 10)->comment('来源平台');
            $table->unsignedBigInteger('id')->comment('识别ID：交易号等');
            $table->string('promotion_id', 255)->nullable()->default('')->comment('优惠ID');
            $table->string('promotion_name', 255)->nullable()->default('')->comment('优惠名称');
            $table->string('promotion_desc', 255)->nullable()->default('')->comment('优惠描述');
            $table->decimal('discount_fee', 8, 2)->nullable()->default(0)->comment('优惠金额');
            $table->string('gift_item_id', 32)->nullable()->default('')->comment('赠品宝贝ID');
            $table->integer('gift_item_num')->nullable()->default(0)->comment('赠品数量');
            $table->timestamps();

            $table->index(['tid', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sys_std_trade_promotion');
    }
}