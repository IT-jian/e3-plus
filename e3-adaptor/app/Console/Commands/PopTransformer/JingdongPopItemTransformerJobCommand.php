<?php


namespace App\Console\Commands\PopTransformer;


use App\Models\JingdongItem;
use App\Services\Adaptor\Jingdong\Jobs\JingdongItemBatchTransferJob;

class JingdongPopItemTransformerJobCommand extends BasePopTransformerJobCommand
{
    protected $signature = 'adaptor:jingdong:pop_item_transformer_job
                            {--from= : 下载开始 M 小时之前，默认为 2 小时前开始}
                            {--to= : 下载结束 N 小时之前， 默认为 1 小时前结束}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将未格式化的京东商品，重新产生格式化JOB';

    protected $popByShop = true;

    public function popJob($from, $to, $shop = [])
    {
        $where = [];
        $where['sync_status'] = 0;
        $where[] = ['origin_modified', '>=', strtotime($from)];
        $where[] = ['origin_modified', '<', strtotime($to)];
        if ($shop['seller_nick']) {
            $where['vender_id'] = $shop['seller_nick'];
        }
        $total = JingdongItem::where($where)->count();
        $this->info('found total: ' . $total);
        if ($total) {
            JingdongItem::select(['ware_id'])->where($where)
                ->chunk(500, function ($results, $page) use ($shop) {
                    $wareIds = $results->pluck('ware_id')->toArray();
                    dispatch(new JingdongItemBatchTransferJob(['ware_ids' => $wareIds, 'shop_code' => $shop['code']]));
                });

            $message = '计划任务找到未及时格式化京东商品：' . $total . "。已经重试处理！";
            $this->sendNotice($message);
        }
    }
}
