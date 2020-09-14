<?php


namespace App\Console\Commands\PopTransformer;


use App\Models\TaobaoItem;
use App\Services\Adaptor\Taobao\Jobs\TaobaoItemBatchTransferJob;

class TaobaoPopItemTransformerJobCommand extends BasePopTransformerJobCommand
{
    protected $signature = 'adaptor:taobao:pop_item_transformer_job
                            {--from= : 下载开始 M 小时之前，默认为 2 小时前开始}
                            {--to= : 下载结束 N 小时之前， 默认为 1 小时前结束}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将指定时间范围内(default: -2h ~ -1h)，未格式化的淘宝商品，重新产生格式化JOB';

    public function popJob($from, $to, $shop = [])
    {
        $where = [];
        $where['sync_status'] = 0;
        $where[] = ['origin_modified', '>=', strtotime($from)];
        $where[] = ['origin_modified', '<', strtotime($to)];
        $total = TaobaoItem::where($where)->count();
        $this->info('found total: ' . $total);
        if ($total) {
            TaobaoItem::select(['num_iid'])->where($where)->chunk(500, function ($results, $page) {
                $numIids = $results->pluck('num_iid')->toArray();
                $key = "pop_item_transformer_job:page" . $page;
                dispatch(new TaobaoItemBatchTransferJob(['num_iid' => $numIids, 'platform' => 'taobao', 'key' => $key]));
            });

            $message = '计划任务找到未及时格式化淘宝商品：' . $total . "。已经重试处理！";
            $this->sendNotice($message);
        }
    }
}
