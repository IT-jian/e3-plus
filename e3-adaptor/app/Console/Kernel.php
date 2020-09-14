<?php

namespace App\Console;

use App\Console\Commands\TaobaoQuantityAsyncUpdateQueuePopCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\GetTaobaoTradeListCommand::class,
        \App\Console\Commands\TestCommand::class,
        \App\Console\Commands\QueueWorkerConfigSyncCommand::class,
        \App\Console\Commands\QueueWorkerConfigReloadCommand::class,
        \App\Console\Commands\ExportTaobaoCommentCommand::class,
        \App\Console\Commands\ExportJingdongCommentCommand::class,
        \App\Console\Commands\PushQueueInitCommand::class,
        \App\Console\Commands\PopInvoiceApplyJobCommand::class,
        \App\Console\Commands\ExportPlatformSkuInventoryCommand::class,
        \App\Console\Commands\PopTransformer\JingdongPopItemTransformerJobCommand::class,
        \App\Console\Commands\PopTransformer\JingdongPopRefundTransformerJobCommand::class,
        \App\Console\Commands\PopTransformer\JingdongPopTradeTransformerJobCommand::class,
        \App\Console\Commands\PopTransformer\TaobaoPopExchangeTransformerJobCommand::class,
        \App\Console\Commands\PopTransformer\TaobaoPopItemTransformerJobCommand::class,
        \App\Console\Commands\PopTransformer\TaobaoPopRefundTransformerJobCommand::class,
        \App\Console\Commands\PopTransformer\TaobaoPopTradeTransformerJobCommand::class,
        \App\Console\Commands\Initial\TaobaoTradeDownloadApiCommand::class,
        \App\Console\Commands\Initial\TaobaoRefundDownloadApiCommand::class,
        \App\Console\Commands\Initial\TaobaoExchangeDownloadApiCommand::class,
        \App\Console\Commands\Initial\JingdongTradeDownloadApiCommand::class,
        \App\Console\Commands\Initial\JingdongServiceDownloadApiCommand::class,
        \App\Console\Commands\DownloadCheck\TaobaoTradeCheckCommand::class,
        \App\Console\Commands\DownloadCheck\TaobaoRefundCheckCommand::class,
        \App\Console\Commands\DownloadCheck\TaobaoExchangeCheckCommand::class,
        \App\Console\Commands\DownloadCheck\JingdongServiceCheckCommand::class,
        \App\Console\Commands\DownloadCheck\JingdongRefundApplyCheckCommand::class,
        \App\Console\Commands\DownloadCheck\JingdongTradeCheckCommand::class,
        \App\Console\Commands\RetryRefundReturnHubApiRequestCommand::class,
        TaobaoQuantityAsyncUpdateQueuePopCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 指定 crontab 驱动
        if (null == config('app.crontab_driver')) {
            return;
        }
        // 非生产环境不执行
        if ('production' != config('app.env')) {
            return;
        }

        $platform = config('adaptor.default');
        // 定时更新 supervisor.conf
        // $schedule->command('adaptor:supervisor:sync');
        // Horizon 快照
        $schedule->command('horizon:snapshot')->everyMinute();
        // 平台SKU库存导出
        $schedule->command('adaptor:export_platform_sku_inventory')->runInBackground()->dailyAt('04:25');
        $schedule->command('adaptor:export_platform_sku_inventory')->runInBackground()->dailyAt('17:00');
        // 淘宝上传评论
        if ('taobao' == $platform) {
            $schedule->command('adaptor:taobao:pop_refund_transformer_job')->runInBackground()->everyTenMinutes();
            $schedule->command('adaptor:taobao:pop_exchange_transformer_job')->runInBackground()->hourly();
            $schedule->command('adaptor:taobao:pop_item_transformer_job')->runInBackground()->hourly();
            $schedule->command('adaptor:taobao:pop_trade_transformer_job')->runInBackground()->everyTenMinutes();
            // 导出评论
            $schedule->command('adaptor:taobao:export_comments')->runInBackground()->dailyAt('02:00');
            // 订单漏单检查
            $schedule->command('adaptor:taobao_check:trade_download')->runInBackground()->everyTenMinutes();
            $schedule->command('adaptor:taobao_check:refund_download')->runInBackground()->everyTenMinutes();
            $schedule->command('adaptor:taobao_check:exchange_download')->runInBackground()->everyTenMinutes();
            // 触发发票查询及推送 1 小时执行一次
            $schedule->command('adaptor:pop_invoice_apply_push')->runInBackground()->hourly();
            // 退货退款审核重试 1 分钟执行一次
            $schedule->command('adaptor:hub_api_request_retry:refund_return')->runInBackground()->everyThirtyMinutes();
            // 每分钟触发一次异步库存同步
            $schedule->command(TaobaoQuantityAsyncUpdateQueuePopCommand::class)->runInBackground()->everyMinute();
        }
        // 京东上传评论
        if ('jingdong' == $platform) {
            $schedule->command('adaptor:jingdong:pop_item_transformer_job')->runInBackground()->hourly();
            $schedule->command('adaptor:jingdong:pop_refund_transformer_job')->runInBackground()->everyTenMinutes();
            $schedule->command('adaptor:jingdong:pop_trade_transformer_job')->runInBackground()->everyTenMinutes();
            // 导出评论
            $schedule->command('adaptor:jingdong:export_comments')->runInBackground()->dailyAt('02:00');
            // 订单漏单检查
            $schedule->command('adaptor:jingdong_check:trade_download')->runInBackground()->everyTenMinutes();
            $schedule->command('adaptor:jingdong_check:service_download')->runInBackground()->everyTenMinutes();
            $schedule->command('adaptor:jingdong_check:refund_apply_download')->runInBackground()->everyTenMinutes();
        }
    }
}
