<?php


namespace App\Console\Commands\PopTransformer;


use App\Models\TaobaoRefund;
use App\Services\Adaptor\Taobao\Jobs\RefundBatchTransferJob;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsTradeRepository;

class TaobaoPopRefundTransformerJobCommand extends BasePopTransformerJobCommand
{
    protected $signature = 'adaptor:taobao:pop_refund_transformer_job
                            {--from= : 下载开始 M 小时之前，默认为 2 小时前开始}
                            {--to= : 下载结束 N 小时之前， 默认为 1 小时前结束}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将指定时间范围内(default: -2h ~ -1h)，未格式化的淘宝退单，重试';

    public function popJob($from, $to, $shop = [])
    {
        $where = [];
        $where['sync_status'] = 0;
        $where[] = ['origin_modified', '>=', strtotime($from)];
        $where[] = ['origin_modified', '<', strtotime($to)];
        $total = TaobaoRefund::where($where)->count();
        $this->info('found total: ' . $total);
        if ($total) {
            TaobaoRefund::select(['refund_id', 'tid'])->where($where)->chunk(500, function ($results, $page) {
                $refundIds = [];
                $tids = $results->pluck('tid')->toArray();
                $rds = new TaobaoRdsTradeRepository();
                $trades = $rds->getAll([['tid', 'IN', $tids]], ['tid']);
                if ($trades->isNotEmpty()) {
                    $existTids = $trades->pluck('tid')->toArray();
                    foreach ($results as $result) {
                        if (!in_array($result['tid'], $existTids)) {
                            continue;
                        }
                        $refundIds[] = $result['refund_id'];
                    }
                }
                if ($refundIds) {
                    $key = "pop_refund_transformer_job:page" . $page;
                    dispatch(new RefundBatchTransferJob(['refund_ids' => $refundIds, 'key' => $key]));
                    $message = '计划任务找到未及时格式化淘宝退单：' . count($refundIds) . "。已经重试处理！" . implode(',', array_chunk($refundIds, 10)[0]);
                    $this->sendNotice($message);
                }
            });
        }
    }
}
