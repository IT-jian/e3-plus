<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaobaoExchangesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taobao_exchange', function (Blueprint $table) {
            $table->unsignedBigInteger('dispute_id')->comment('换货单号');
            $table->string('seller_nick', 50)->comment('平台店铺');
            $table->unsignedBigInteger('biz_order_id')->comment('交易号');
            $table->string('status', 32)->comment('状态');
            $table->mediumText('origin_content')->nullable()->comment('原始报文');
            $table->unsignedInteger('origin_created')->nullable()->comment('报文创建时间');
            $table->unsignedInteger('origin_modified')->nullable()->comment('报文更新时间');
            $table->datetime('created')->nullable()->comment('创建时间');
            $table->datetime('modified')->nullable()->comment('更新时间');
            $table->unsignedTinyInteger('sync_status')->default(0)->comment('0:未转入,1:已转入,2:锁定中');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->primary('dispute_id');
            $table->index('biz_order_id');
            $table->index('status');
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
        Schema::drop('taobao_exchange');
    }
}
