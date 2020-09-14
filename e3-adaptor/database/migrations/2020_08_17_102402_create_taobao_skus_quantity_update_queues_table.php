<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaobaoSkusQuantityUpdateQueuesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taobao_skus_quantity_update_queue', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment('ID');
            $table->unsignedBigInteger('num_iid')->comment('商品ID');
            $table->unsignedBigInteger('sku_id')->comment('SKU ID');
            $table->string('shop_code', 20)->comment('店铺编码');
            $table->tinyInteger('update_type')->default(1)->comment('更新方式: 1 全量，2 增量 默认全量');
            $table->unsignedBigInteger('sku_version')->nullable()->comment('sku库存版本:13位时间戳');
            $table->unsignedBigInteger('batch_version')->nullable()->comment('更新批次版本:10位时间戳');
            $table->tinyInteger('status')->default(0)->comment('更新状态:0 初始，1 成功 2 锁定 3 失败重试 4 失败不重试');
            $table->tinyInteger('try_times')->default(0)->comment('重试次数');
            $table->string('message', 1024)->nullable()->comment('失败信息');
            $table->timestamps();

            $table->index('num_iid');
            $table->index('sku_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('taobao_skus_quantity_update_queue');
    }
}
