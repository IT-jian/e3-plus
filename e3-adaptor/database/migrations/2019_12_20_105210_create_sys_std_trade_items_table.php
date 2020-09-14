<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysStdTradeItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_trade_item', function (Blueprint $table) {
            $table->unsignedBigInteger('tid')->comment('交易号');
            $table->string('platform', 10)->comment('来源平台');
            $table->unsignedTinyInteger('row_index')->comment('行号');
            $table->string('title')->nullable()->default('')->comment('商品名称');
            $table->string('color', 64)->nullable()->default('')->comment('颜色');
            $table->string('size', 64)->nullable()->default('')->comment('尺码');
            $table->unsignedBigInteger('sku_id')->comment('平台sku id');
            $table->unsignedBigInteger('oid')->nullable()->default(0)->comment('子订单号');
            $table->unsignedBigInteger('num_iid')->nullable()->default(0)->comment('商品数字ID');
            $table->string('outer_iid', 32)->default('')->comment('商家外部编码');
            $table->string('outer_sku_id', 32)->default('')->comment('商家SKU ID');
            $table->integer('num')->default(1)->comment('数量');
            $table->decimal('price', 8, 2)->nullable()->default(0)->comment('商品单价');
            $table->decimal('total_fee', 8, 2)->nullable()->default(0)->comment('商品金额');
            $table->decimal('discount_fee', 8, 2)->nullable()->default(0)->comment('优惠金额');
            $table->decimal('adjust_fee', 8, 2)->nullable()->default(0)->comment('手工调整金额');
            $table->decimal('part_mjz_discount', 8, 2)->nullable()->default(0)->comment('优惠分摊');
            $table->decimal('payment', 8, 2)->nullable()->default(0)->comment('支付金额');
            $table->decimal('divide_order_fee', 8, 2)->nullable()->default(0)->comment('分摊之后的实付金额');
            $table->timestamps();

            $table->index(['tid','platform']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_std_trade_item');
    }
}
