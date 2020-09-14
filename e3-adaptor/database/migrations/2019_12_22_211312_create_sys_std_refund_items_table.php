<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdRefundItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_refund_item', function (Blueprint $table) {
            $table->unsignedBigInteger('refund_id')->comment('退单号');
            $table->unsignedBigInteger('oid')->comment('子单号');
            $table->string('platform', 10)->comment('来源平台');
            $table->unsignedTinyInteger('row_index')->comment('行号');
            $table->unsignedBigInteger('sku_id')->nullable()->default(0)->comment('平台sku id');
            $table->unsignedBigInteger('num_iid')->comment('商品数字ID');
            $table->string('outer_iid', 32)->nullable()->default('')->comment('商家外部编码');
            $table->string('outer_sku_id', 32)->default('')->comment('商家SKU ID');
            $table->unsignedInteger('num')->comment('数量');
            $table->string('reason', 64)->comment('退货原因');
            $table->string('desc', 255)->comment('退货描述');
            $table->timestamps();

            $table->index(['refund_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sys_std_refund_item');
    }
}