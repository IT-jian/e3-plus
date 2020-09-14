<?php


namespace App\Console\Commands\PopTransformer;


use App\Models\TaobaoTrade;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;

class TaobaoPopTradeTransformerJobCommand extends BasePopTransformerJobCommand
{
    protected $signature = 'adaptor:taobao:pop_trade_transformer_job
                            {--from= : 下载开始 M 小时之前，默认为 2 小时前开始}
                            {--to= : 下载结束 N 小时之前， 默认为 1 小时前结束}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将指定时间范围内(default: -2h ~ -1h)的淘宝未格式化订单，重试格式化';

    public function popJob($from, $to, $shop = [])
    {
        $where = [];
        $where['sync_status'] = 0;
        $where[] = ['origin_modified', '>=', strtotime($from)];
        $where[] = ['origin_modified', '<', strtotime($to)];
        $total = TaobaoTrade::where($where)->count();
        $this->info('found total: ' . $total);
        if ($total) {
            TaobaoTrade::select(['tid'])->where($where)->chunk(500, function ($results, $page) {
                $orderIds = $results->pluck('tid')->toArray();
                $key = "pop_trade_transformer_job:page" . $page;
                dispatch(new TaobaoTradeBatchTransferJob(['tids' => $orderIds, 'key' => $key]));
            });

            $message = '计划任务找到未及时格式化淘宝订单：' . $total . "。已经重试处理！";
            $this->sendNotice($message);
        }
    }
}
