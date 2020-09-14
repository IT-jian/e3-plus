<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaobaoItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taobao_item', function (Blueprint $table) {
            $table->unsignedBigInteger('num_iid')->comment('退单号');
            $table->string('seller_nick', 50)->comment('平台店铺');
            $table->string('status', 32)->comment('商品状态');
            $table->mediumText('origin_content')->nullable()->comment('原始报文');
            $table->unsignedInteger('origin_created')->nullable()->comment('报文创建时间');
            $table->unsignedInteger('origin_modified')->nullable()->comment('报文更新时间');
            $table->unsignedTinyInteger('origin_delete')->default(0)->comment('0:正常,1:已删除');
            $table->unsignedTinyInteger('sync_status')->default(0)->comment('0:未转入,1:已转入,2:锁定中');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->primary('num_iid');
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
        Schema::drop('taobao_item');
    }
}