<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaobaoCommentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taobao_comment', function (Blueprint $table) {
            $table->unsignedBigInteger('tid')->comment('交易号');
            $table->unsignedBigInteger('oid')->comment('子交易号');
            $table->string('num_iid', 50)->nullable()->comment('商品数字ID');
            $table->string('seller_nick', 50)->nullable()->comment('平台店铺');
            $table->timestamp('created')->nullable()->comment('创建时间');
            $table->mediumText('origin_content')->comment('原始报文');
            $table->unsignedTinyInteger('sync_status')->default(0)->nullable()->comment('转入状态');
            $table->timestamps();

            $table->primary(['tid', 'oid', 'num_iid']);
            $table->index('created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('taobao_comment');
    }
}
