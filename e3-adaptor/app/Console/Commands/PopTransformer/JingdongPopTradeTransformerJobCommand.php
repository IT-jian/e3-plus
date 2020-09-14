<?php


namespace App\Console\Commands\PopTransformer;


use App\Models\JingdongTrade;
use App\Services\Adaptor\Jingdong\Jobs\JingdongTradeBatchTransferJob;

class JingdongPopTradeTransformerJobCommand extends BasePopTransformerJobCommand
{
    protected $signature = 'adaptor:jingdong:pop_trade_transformer_job
                            {--from= : 下载开始 M 小时之前，默认为 2 小时前开始}
                            {--to= : 下载结束 N 小时之前， 默认为 1 小时前结束}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将未格式化的京东rds订单，重试';

    public function popJob($from, $to, $shop = [])
    {

        $where = [];
        $where['sync_status'] = 0;
        $where[] = ['origin_modified', '>=', strtotime($from)];
        $where[] = ['origin_modified', '<', strtotime($to)];
        $total = JingdongTrade::where($where)->count();
        $this->info('found total: ' . $total);
        if ($total) {
            JingdongTrade::select(['order_id'])->where($where)->chunk(500, function ($results, $page) {
                    $orderIds = $results->pluck('order_id')->toArray();
                    $key = "pop_trade_transformer_job:page" . $page;
                    dispatch(new JingdongTradeBatchTransferJob(['order_ids' => $orderIds, 'key' => $key]));
                });
            $message = '计划任务找到未及时格式化京东订单：' . $total . "。已经重试处理！";
            $this->sendNotice($message);
        }
    }
}
