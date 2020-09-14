<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdExchangeItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_exchange_item', function (Blueprint $table) {
            $table->unsignedBigInteger('dispute_id')->comment('换货单号');
            $table->unsignedBigInteger('oid')->nullable()->comment('子订单号');
            $table->string('platform', 10)->comment('来源平台');
            $table->unsignedTinyInteger('row_index')->comment('订单明细行号');
            $table->string('goods_status', 20)->nullable()->default('')->comment('商品状态');
            $table->unsignedBigInteger('bought_sku')->comment('购买SKU ID');
            $table->unsignedBigInteger('bought_num_iid')->comment('购买商品ID');
            $table->string('bought_outer_iid', 32)->nullable()->default('')->comment('购买商家外部编码');
            $table->string('bought_outer_sku_id', 32)->default('')->comment('数字商家SKU ID');
            $table->string('bought_color', 32)->default('')->comment('颜色');
            $table->string('bought_size', 32)->default('')->comment('尺码');
            $table->unsignedBigInteger('exchange_sku')->comment('换货SKU');
            $table->string('title')->nullable()->default('')->comment('换货商品名称');
            $table->unsignedBigInteger('exchange_num_iid')->default('')->comment('换货商品ID');
            $table->string('exchange_outer_iid', 32)->nullable()->default('')->comment('换货商家外部编码');
            $table->string('exchange_outer_sku_id', 32)->default('')->comment('换货数字商家SKU ID');
            $table->string('exchange_color', 32)->default('')->comment('换货颜色');
            $table->string('exchange_size', 32)->default('')->comment('换货尺码');
            $table->unsignedInteger('num')->comment('换货数量');
            $table->decimal('price', 8, 2)->nullable()->default(0)->comment('金额');
            $table->string('reason', 32)->nullable()->default('')->comment('换货原因');
            $table->string('desc', 32)->nullable()->default('')->comment('原因说明');
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
        Schema::drop('sys_std_exchange_item');
    }
}