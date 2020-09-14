<?php


namespace App\Console\Commands;


use App\Jobs\DingTalkNoticeTextSendJob;
use App\Services\Hub\Jobs\TradeInvoiceDetailQueryAndCreateBatchJob;
use Illuminate\Console\Command;

class PopInvoiceApplyJobCommand extends Command
{
    protected $name = 'adaptor:pop_invoice_apply_push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '淘宝发票推送重试接口';

    public function handle()
    {
        $fields = ['taobao_invoice_apply.apply_id', 'taobao_invoice_apply.platform_tid'];
        $where[] = ['sys_std_trade.status',  'TRADE_FINISHED'];
        $where[] = ['taobao_invoice_apply.push_status',  0];
        $where[] = ['sys_std_trade.pay_time', '>=', '2020-07-21 10:00:00'];
        $applies = \DB::table('taobao_invoice_apply')->leftJoin('sys_std_trade', function ($join) {
            $join->on('taobao_invoice_apply.platform_tid', '=', 'sys_std_trade.tid');
        })->select($fields)->where($where)->get();
        $this->info('found applies ' . count($applies));
        if ($applies->isNotEmpty()) {
            $applyIds = $applies->pluck('apply_id')->toArray();
            foreach (array_chunk($applyIds, 3) as $key => $chunkIds) {
                dispatch(new TradeInvoiceDetailQueryAndCreateBatchJob($chunkIds));
            }
        }
        $message = sprintf('淘宝发票推送重试接口运行，查询到待推送已完成订单：%s', count($applies));
        dispatch(new DingTalkNoticeTextSendJob(['message' => $message]));
        $this->info('处理完成，已经推送到队列' . count($applies));
    }
}
