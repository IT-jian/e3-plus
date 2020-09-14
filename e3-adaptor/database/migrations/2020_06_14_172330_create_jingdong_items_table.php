<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJingdongItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jingdong_item', function (Blueprint $table) {
            $table->unsignedBigInteger('ware_id')->comment('商品ID');
            $table->string('vender_id', 50)->comment('平台店铺');
            $table->string('ware_status', 10)->default(0)->comment('商品状态 1:上架 2:下架 4:删除');
            $table->json('origin_content')->nullable()->comment('原始报文');
            $table->unsignedInteger('origin_created')->nullable()->comment('报文创建时间');
            $table->unsignedInteger('origin_modified')->nullable()->comment('报文创建时间');
            $table->unsignedTinyInteger('sync_status')->default(0)->comment('0:未转入,1:已转入,2:锁定中');

            $table->timestamps();

            $table->primary('ware_id');
            $table->index('vender_id');
            $table->index('origin_modified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('jingdong_item');
    }
}
