<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJingdongSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jingdong_sku', function (Blueprint $table) {
            $table->unsignedBigInteger('sku_id')->primary()->comment('平台sku_id');
            $table->string('vender_id', 50)->comment('平台店铺');
            $table->string('shop_code', 50)->index()->comment('店铺');
            $table->string('sku_name', 255)->default('')->nullable()->comment('sku名称');
            $table->string('outer_id', 50)->default('')->nullable()->index()->comment('商家编号');
            $table->string('barcode', 50)->default('')->nullable()->comment('条码');
            $table->string('ware_id', 50)->comment('平台货号');
            $table->string('ware_title', 255)->default('')->nullable()->comment('商品名称');
            $table->string('category_id', 255)->default('')->nullable()->comment('三级类目id');
            $table->unsignedTinyInteger('status')->default(0)->nullable()->comment('商品状态');
            $table->decimal('jd_price', 8, 2)->nullable()->comment('京东价');
            $table->timestamp('created')->nullable()->comment('平台创建时间');
            $table->timestamp('modified')->nullable()->comment('平台更新时间');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('jingdong_sku');
    }
}
