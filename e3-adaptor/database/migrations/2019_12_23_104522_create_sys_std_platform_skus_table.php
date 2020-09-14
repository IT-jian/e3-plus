<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdPlatformSkusTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_platform_sku', function (Blueprint $table) {
            $table->string('platform', 10)->comment('来源平台');
            $table->string('shop_code', 20)->comment('商店编码');
            $table->string('sku_id', 32)->comment('平台 SKU ID');
            $table->string('num_iid', 32)->comment('商品ID: num_iid');
            $table->string('title', 64)->comment('商品名称');
            $table->string('color', 32)->nullable()->comment('颜色');
            $table->string('size', 32)->nullable()->comment('尺码');
            $table->string('approve_status', 32)->comment('状态：onsale出售中，instock库中');
            $table->string('outer_id', 32)->nullable()->comment('商家SKU外部编码');
            $table->string('outer_iid', 32)->nullable()->comment('商家商品外部编码');
            $table->string('barcode', 32)->nullable()->comment('sku条码');
            $table->integer('quantity')->nullable()->default(0)->comment('库存数量');
            $table->decimal('price', 8, 2)->nullable()->comment('金额');
            $table->unsignedTinyInteger('is_delete')->nullable()->default(0)->comment('是否已经删除: 0 正常,1 已删除');
            $table->datetime('created')->comment('创建时间');
            $table->datetime('modified')->comment('平台更新时间');
            $table->timestamp('created_at')->nullable()->comment('下载时间');
            $table->timestamp('updated_at')->nullable()->useCurrent()->comment('更新时间');

            $table->primary(['platform', 'shop_code', 'sku_id']);
            $table->index('outer_id');
            $table->index('modified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sys_std_platform_sku');
    }
}
