<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJingdongOrderSplitAmountsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jingdong_order_split_amount', function (Blueprint $table) {
            $table->string('order_id', 50)->primary()->comment('交易号');
            $table->string('vender_id', 50)->comment('店铺ID');
            $table->mediumText('origin_content')->comment('原始报文');
            $table->unsignedInteger('origin_created')->comment('报文创建时间');
            $table->unsignedTinyInteger('sync_status')->default(0)->nullable()->comment('转入状态');
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
        Schema::drop('jingdong_order_split_amount');
    }
}
