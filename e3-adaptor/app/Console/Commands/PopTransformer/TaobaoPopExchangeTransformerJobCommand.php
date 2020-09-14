<?php


namespace App\Console\Commands\PopTransformer;


use App\Models\TaobaoExchange;
use App\Services\Adaptor\Taobao\Jobs\ExchangeBatchTransferJob;

class TaobaoPopExchangeTransformerJobCommand extends BasePopTransformerJobCommand
{
    protected $signature = 'adaptor:taobao:pop_exchange_transformer_job
                            {--from= : 下载开始 M 小时之前，默认为 2 小时前开始}
                            {--to= : 下载结束 N 小时之前， 默认为 1 小时前结束}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将指定时间范围内(default: -2h ~ -1h)，未格式化的淘宝商品,重试格式化';

    protected $popByShop = true;

    public function popJob($from, $to, $shop = [])
    {
        $where = [];
        $where['sync_status'] = 0;
        $where[] = ['origin_modified', '>=', strtotime($from)];
        $where[] = ['origin_modified', '<', strtotime($to)];
        if ($shop['seller_nick']) {
            $where['seller_nick'] = $shop['seller_nick'];
        }
        $total = TaobaoExchange::where($where)->count();
        $this->info($shop['code'] . 'found total: ' . $total);
        if ($total) {
            TaobaoExchange::select(['dispute_id'])->where($where)
                ->chunk(500, function ($results, $page) use ($shop) {
                    $disputeIds = $results->pluck('dispute_id')->toArray();
                    dispatch(new ExchangeBatchTransferJob(['dispute_ids' => $disputeIds, 'shop_code' => $shop['code']]));
                });
            $message = '计划任务找到未及时格式化淘宝换货单：' . $total . "。已经重试处理！";
            $this->sendNotice($message);
        }
    }
}
