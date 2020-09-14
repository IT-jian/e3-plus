<?php


namespace App\Console\Commands\PopTransformer;


use App\Models\JingdongRefund;
use App\Services\Adaptor\Jingdong\Jobs\JingdongExchangeTransferJob;
use App\Services\Adaptor\Jingdong\Jobs\JingdongRefundTransferJob;

class JingdongPopRefundTransformerJobCommand extends BasePopTransformerJobCommand
{
    protected $signature = 'adaptor:jingdong:pop_refund_transformer_job
                            {--from= : 下载开始 M 小时之前，默认为 2 小时前开始}
                            {--to= : 下载结束 N 小时之前， 默认为 1 小时前结束}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将未格式化的京东服务单，重新产生格式化JOB';

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
        $total = JingdongRefund::where($where)->count();
        $this->info('found total: ' . $total);
        if ($total) {
            $count = 0;
            $jingdongRefunds = JingdongRefund::select(['service_id', 'customer_expect', 'change_sku'])->where($where)->get();
            foreach ($jingdongRefunds as $refund) {
                $refund['shop_code'] = $shop['code'];
                if (10 == $refund['customer_expect']) {
                    dispatch(new JingdongRefundTransferJob($refund)); // 格式化订单
                    $count++;
                } else if (20 == $refund['customer_expect'] && !empty($refund['change_sku'])) {
                    dispatch(new JingdongExchangeTransferJob($refund)); // 格式化退单
                    $count++;
                }
            }

            if ($count) {
                $message = '计划任务找到未及时格式化京东服务单：' . $count . "。已经重试处理！";
                $this->sendNotice($message);
            }
        }
    }
}
