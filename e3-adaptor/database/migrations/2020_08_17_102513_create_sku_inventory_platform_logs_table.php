<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSkuInventoryPlatformLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sku_inventory_platform_log', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment('ID');
            $table->unsignedBigInteger('num_iid')->comment('商品ID');
            $table->string('shop_code', 20)->comment('店铺编码');
            $table->json('skus')->nullable()->comment('sku内容');
            $table->tinyInteger('update_type')->default(1)->comment('更新方式: 1 全量，2 增量 默认全量');
            $table->unsignedBigInteger('batch_version')->nullable()->comment('请求版本');
            $table->json('request')->nullable()->comment('请求内容');
            $table->json('response')->nullable()->comment('响应内容');
            $table->timestamp('start_at')->nullable()->comment('请求时间');
            $table->timestamp('end_at')->nullable()->comment('响应时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sku_inventory_platform_log');
    }
}
