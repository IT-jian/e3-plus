<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJingdongCommentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jingdong_comment', function (Blueprint $table) {
            $table->string('comment_id', 64)->comment('平台ID');
            $table->string('order_id', 50)->comment('交易号');
            $table->string('vender_id', 50)->comment('商家编号');
            $table->string('sku_id', 64)->comment('skuId');
            $table->mediumText('origin_content')->comment('原始报文');
            $table->unsignedInteger('origin_created')->comment('报文创建时间');
            $table->unsignedInteger('origin_modified')->comment('报文更新时间');
            $table->unsignedTinyInteger('sync_status')->default(0)->nullable()->comment('转入状态');
            $table->timestamp('create_time')->nullable()->comment('平台创建时间');
            $table->timestamps();

            $table->primary('comment_id');
            $table->index(['order_id', 'sku_id'], 'ind_order_sku');
            $table->index('vender_id');
            $table->index('sync_status');
            $table->index('origin_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('jingdong_comment');
    }
}