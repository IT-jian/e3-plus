<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaobaoInvoiceAppliesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taobao_invoice_apply', function (Blueprint $table) {
            $table->unsignedBigInteger('apply_id')->primary()->comment('申请ID');
            $table->string('platform_tid', 50)->comment('交易号');
            $table->string('seller_nick', 50)->comment('平台店铺');
            $table->string('platform_code', 10)->comment('平台代码');
            $table->string('trigger_status', 32)->comment('触发类型');
            $table->unsignedTinyInteger('business_type')->default(0)->comment('抬头类型');
            $table->unsignedTinyInteger('query_status')->default(0)->comment('状态: 0 待查询 1 已查询申请');
            $table->timestamp('query_at')->nullable()->comment('查询时间');
            $table->timestamp('next_query_at')->nullable()->comment('下次查询发票申请时间');
            $table->unsignedTinyInteger('push_status')->default(0)->comment('状态: 0 待推送 1 推送成功');
            $table->timestamp('pushed_at')->nullable()->comment('推送时间');
            $table->unsignedTinyInteger('upload_status')->default(0)->comment('状态: 0 待上传 1 上传成功 2 上传失败');
            $table->timestamp('upload_at')->nullable()->comment('上传时间');
            $table->json('origin_content')->nullable()->comment('申请报文');
            $table->json('origin_detail')->nullable()->comment('详情报文');
            $table->json('origin_upload_detail')->nullable()->comment('上传报文');
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
        Schema::drop('taobao_invoice_apply');
    }
}
