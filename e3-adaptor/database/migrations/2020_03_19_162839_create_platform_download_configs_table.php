<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlatformDownloadConfigsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_download_config', function (Blueprint $table) {
            $table->integer('id', true)->comment('ID');
            $table->string('platform', 10)->comment('平台');
            $table->string('code', 50)->comment('编码');
            $table->unsignedTinyInteger('type')->default(1)->comment('类型：1. rds 2. api');
            $table->string('name', 50)->comment('名称');
            $table->unsignedTinyInteger('stop_download')->default(1)->comment('停止下载');
            $table->integer('query_page_size')->comment('单次查询数量');
            $table->integer('job_page_size')->comment('单个任务批数量');
            $table->integer('next_query_at')->comment('下次查询开始时间');
            $table->timestamps();
            $table->unique('code');
        });
        $this->initData();
    }

    public function initData()
    {

        $configs = [
            [
                'platform'        => 'taobao',
                'code'            => 'taobao_rds_trade_sync_jobs',
                'name'            => '淘宝RDS订单下载',
                'stop_download'   => '1',
                'type'   => '1',
                'query_page_size' => '5000',
                'job_page_size'   => '500',
            ],
            [
                'platform'        => 'taobao',
                'code'            => 'taobao_rds_refund_sync_jobs',
                'name'            => '淘宝RDS退单下载',
                'stop_download'   => '1',
                'type'            => '1',
                'query_page_size' => '5000',
                'job_page_size'   => '500',
            ],
            [
                'platform'        => 'taobao',
                'code'            => 'taobao_exchange_sync_jobs',
                'name'            => '淘宝换货单下载-接口',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '30',
                'job_page_size'   => '1',
            ],
//            [
//                'platform'        => 'taobao',
//                'code'            => 'taobao_rds_item_sync_jobs',
//                'name'            => '淘宝RDS商品下载',
//                'stop_download'   => '1',
//                'type'            => '1',
//                'query_page_size' => '500',
//                'job_page_size'   => '100',
//            ],
            [
                'platform'        => 'taobao',
                'code'            => 'taobao_comment_sync_jobs',
                'name'            => '淘宝订单评论下载-接口',
                'stop_download'   => '1',
                'type'            => '2',
                'query_page_size' => '100',
                'job_page_size'   => '100',
            ],
            [
                'platform'        => 'taobao',
                'code'            => 'taobao_invoice_apply_sync_jobs',
                'name'            => '淘宝开票申请监听-接口',
                'stop_download'   => '1',
                'type'            => '2',
                'query_page_size' => '200',
                'job_page_size'   => '200',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_rds_trade_sync_jobs',
                'name'            => '京东RDS订单下载',
                'stop_download'   => '1',
                'type'            => '1',
                'query_page_size' => '5000',
                'job_page_size'   => '500',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_step_trade_sync_jobs',
                'name'            => '京东预售订单下载',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '500',
                'job_page_size'   => '100',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_refund_sync_jobs',
                'name'            => '京东待收货退单下载',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '50',
                'job_page_size'   => '50',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_refund_apply_sync_jobs',
                'name'            => '京东退款申请下载--申请时间',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '50',
                'job_page_size'   => '50',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_refund_apply_check_sync_jobs',
                'name'            => '京东退款申请下载--审核时间',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '50',
                'job_page_size'   => '50',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_refund_update_sync_jobs',
                'name'            => '京东待收货退单更新',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '50',
                'job_page_size'   => '50',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_refund_freight_update_sync_jobs',
                'name'            => '京东服务单运单更新',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '50',
                'job_page_size'   => '50',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_comments_sync_jobs',
                'name'            => '京东会员评论',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '50',
                'job_page_size'   => '50',
            ],
            [
                'platform'        => 'jingdong',
                'code'            => 'jingdong_item_sync_jobs',
                'name'            => '京东商品下载',
                'type'            => '2',
                'stop_download'   => '1',
                'query_page_size' => '50',
                'job_page_size'   => '50',
            ],
        ];
        \App\Models\PlatformDownloadConfig::insert($configs);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('platform_download_config');
    }
}
