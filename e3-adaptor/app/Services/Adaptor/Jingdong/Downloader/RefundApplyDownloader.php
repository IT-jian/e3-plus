<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Facades\Adaptor;
use App\Models\AdidasWmsQueue;
use App\Models\Sys\Shop;
use App\Models\SysStdRefund;
use App\Models\SysStdTrade;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Listeners\JingdongPushQueueTrait;
use App\Services\Adaptor\Jingdong\Repository\JingdongRefundApplyRepository;
use App\Services\Hub\HubRequestEnum;
use App\Services\Wms\Jobs\AdidasWmsPushJob;
use Event;
use Illuminate\Support\Carbon;

class RefundApplyDownloader implements DownloaderContract
{
    use JingdongPushQueueTrait;

    private $repository;

    public function __construct(JingdongRefundApplyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function download($refunds)
    {
        $shopMap = [];
        $refundApplies = $this->saveRefundApplies($refunds);
        $shops = Shop::all();
        foreach ($shops as $shop) {
            $shopMap[$shop['seller_nick']] = $shop['code'];
        }
        $queue = $successApplyIds = [];
        foreach ($refundApplies as $refundApply) {
            $existTrade = SysStdTrade::platform('jingdong')->where('tid', $refundApply['order_id'])->first();
            if (empty($existTrade)) {
                $params = ['order_id' => $refundApply['order_id'], 'shop_code' => $shopMap[$refundApply['vender_id']]];
                Adaptor::platform('jingdong')->download('tradeApi', $params);
                Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::TRADE, $params);
                $existTrade = SysStdTrade::platform('jingdong')->where('tid', $refundApply['order_id'])->first();
            }
            // 待审核的退款申请触发更新 取消事件 WAIT_SELLER_STOCK_OUT, LOCKED, TRADE_CANCELED
            if ($existTrade && in_array($refundApply['status'], [0, 3]) && in_array($existTrade['status'], ['WAIT_SELLER_STOCK_OUT','LOCKED','TRADE_CANCELED'])) {
                // @todo 查询是否已经订单，还未推送订单，订单优先入列
                $formatQueue = $this->formatQueue($refundApply['order_id'], HubRequestEnum::TRADE_CANCEL);
                $queue[] = $formatQueue;
            }
            $refund = SysStdRefund::where('tid', $refundApply['order_id'])->where('refund_id', $refundApply['id'])->first();
            if (empty($refund)) {
                try {
                    $stdRefund = $this->formatSysStdRefund($refundApply);
                    SysStdRefund::insert($stdRefund);
                } catch (\Exception $e) {
                    \Log::error('refund apply transfer std refund error:' . $e->getMessage());
                }
            } else {
                $stdRefund = $this->formatSysStdRefund($refundApply);
                unset($stdRefund['created_at']);
                $refund->fill($stdRefund)->save();
            }
            if (in_array($refundApply['status'], [0, 3])) { //  退款申请单状态 0、未审核 1、审核通过2、审核不通过 3、京东财务审核通过 4、京东财务审核不通过 5、人工审核通过 6、拦截并退款 7、青龙拦截成功 8、青龙拦截失败 9、强制关单并退款 10、物流待跟进 11、用户撤销。
                $successApplyIds[] = $refundApply['id'];
            }
        }
        if ($queue) {
            $this->pushQueue($queue);
        }
        // 推送wms任务
        if ($successApplyIds) {
            dispatch(new AdidasWmsPushJob($successApplyIds));
        }
    }

    public function saveRefundApplies($refunds)
    {
        $formatData = [];
        foreach ($refunds as $refund) {
            $formatData[] = $this->format($refund);
        }
        $updateFields = ['status', 'origin_content', 'reason', 'check_time', 'origin_modified', 'sync_status'];
        if ($formatData) {
            $this->repository->insertMulti($formatData, $updateFields);
        }

        return $formatData;
    }

    public function format($refund)
    {
        return [
            'id' => $refund['id'],
            'vender_id' => $refund['vender_id'],
            'order_id' => $refund['orderId'],
            'status' => $refund['status'],
            'reason' => $refund['reason'],
            'apply_time' => $refund['applyTime'],
            'check_time' => $refund['checkTime'] ?? $refund['applyTime'],
            'origin_content' => json_encode($refund),
            'origin_created' => time(),
            'origin_modified' => time(),
            'sync_status' => 0,
        ];
    }

    /**
     * 格式化为退款单
     *
     * @param $refundApply
     * @return array
     */
    public function formatSysStdRefund($refundApply)
    {
        $trade = SysStdTrade::select(['shop_code', 'status', 'payment'])->where('tid', $refundApply['order_id'])->firstOrFail();

        return [
            'platform'        => 'jingdong',
            'refund_id'       => $refundApply['id'],
            'tid'             => $refundApply['order_id'],
            'oid'             => 0,
            'shop_code'       => $trade['shop_code'],
            'status'          => $refundApply['status'],
            'order_status'    => $trade['status'] ?? '',
            'refund_phase'    => 'onsale',
            'refund_version'  => Carbon::now()->timestamp,
            'refund_fee'      => $trade['payment'],
            'company_name'    => '',
            'sid'             => '',
            'has_good_return' => 0,
            'created'         => $refundApply['apply_time'],
            'modified'        => $refundApply['check_time'] ?? $refundApply['apply_time'],
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
    }
}
