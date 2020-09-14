<?php


namespace App\Services\Adaptor\Taobao\Transformer;


use App\Models\Sys\Shop;
use App\Models\SysStdRefund;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Taobao\Events\TaobaoRefundCreateEvent;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\TradeBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\RefundBatchTransferJob;
use App\Services\Adaptor\Taobao\Jobs\RefundTransferJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use DB;
use Event;
use Exception;
use InvalidArgumentException as InvalidArgumentExceptionAlias;
use Log;

class RefundBatchTransformer extends RefundTransformer implements TransformerContract
{
    const PLATFORM = 'taobao';

    /**
     * @var string
     */
    protected $shopCode;

    /**
     * 单个转入
     *
     * @param $params ['refund_id' => '']
     * @return bool
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/10 15:18
     */
    public function transfer($params)
    {
        if (empty($params['refund_ids'])) {
            throw new InvalidArgumentExceptionAlias('refund_ids required!');
        }

        $where = [];
        $refundIs = $params['refund_ids'];
        $where[] = ['refund_id', 'IN', $refundIs];
        $taobaoRefunds = $this->refund->getAll($where);
        if (empty($taobaoRefunds)) {
            return false;
        }

        $sellerNickShopCodeMap = [];
        $shops = Shop::available(self::PLATFORM)->get();
        foreach ($shops as $shop) {
            $sellerNickShopCodeMap[$shop['seller_nick']] = $shop['code'];
        }
        if (empty($sellerNickShopCodeMap)) {
            throw new Exception('店铺不存在，请添加店铺后执行');
        }

        $existRefunds = SysStdRefund::where(['platform' => self::PLATFORM])->whereIn('refund_id', $refundIs)->get();
        if (!$existRefunds->isEmpty()) {
            $existRefunds = $existRefunds->keyBy('refund_id');
        }
        // 退单对应的订单未转入，延迟退单转入
        $diffRefundIds = [];
        $tids = $taobaoRefunds->pluck('tid')->toArray();
        if ($tids) {
            $existTrades = SysStdTrade::where(['platform' => self::PLATFORM])->whereIn('tid', $tids)->get(['tid']);
            if (!$existTrades->isEmpty() && ($existTrades->count() < count($tids))) {
                $diffTids = $existTrades->pluck('tid')->diff($tids)->toArray();
                $diffRefundIds = $taobaoRefunds->filter(function ($refund) use ($diffTids) {
                    return in_array($refund->tid, $diffTids);
                })->pluck('refund_id')->toArray();
            } else if ($existTrades->isEmpty()) { // 不存在
                $diffRefundIds = $taobaoRefunds->pluck('refund_id')->toArray();
            }
        }
        $updateRefunds = $insertRefunds = $notOurTrades = $delayTrades = $skipRefunds = [];
        $updateSuccess = [];
        foreach ($taobaoRefunds as $taobaoRefund) {
            $this->shopCode = $sellerNickShopCodeMap[$taobaoRefund->seller_nick] ?? '';
            if (empty($this->shopCode)) {
                $notOurTrades[] = $taobaoRefund->refund_id;
                continue;
            }
            // 等待订单下载的任务
            if (!empty($diffRefundIds) && in_array($taobaoRefund->refund_id, $diffRefundIds)) {
                $delayTrades[$taobaoRefund->refund_id] = $taobaoRefund->tid;
                continue;
            }
            if (!$taobaoRefunds->isEmpty() && isset($existRefunds[$taobaoRefund->refund_id])) {
                if (isset($taobaoRefund->modified) && !empty($taobaoRefund->modified) && strtotime($existRefunds[$taobaoRefund->refund_id]->modified) >= strtotime($taobaoRefund->modified)) {
                    $skipRefunds[] = $taobaoRefund->refund_id;
                    continue;
                }
                try {
                    // 更新订单
                    $this->updateStdRefund($this->format($taobaoRefund), $existRefunds[$taobaoRefund->refund_id]);
                    $updateSuccess[] = $taobaoRefund->refund_id;
                } catch (Exception $e) {
                    $updateRefunds[$taobaoRefund->refund_id] = [
                        'shop_code' => $this->shopCode,
                        'refund_id' => $taobaoRefund->refund_id,
                    ];
                }
            } else {
                try {
                    $insertRefunds[$taobaoRefund->refund_id] = $this->format($taobaoRefund);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        if ($updateSuccess) {
            $this->refund->updateSyncStatus($updateSuccess, 1);
        }
        // 批量插入
        if (!empty($insertRefunds)) {
            $result = $this->batchInsertStdRefunds($insertRefunds);
            if (!$result) {
                throw new Exception('批量格式化淘宝退单失败');
            }
        }
        // 更新失败 推送到 单个转入队列中处理
        foreach ($updateRefunds as $updateRefund) {
            dispatch(new RefundTransferJob(['refund_id' => $updateRefund['refund_id'], 'shop_code' => $updateRefund['shop_code']]));
        }
        if ($skipRefunds) {
            $this->refund->updateSyncStatus($skipRefunds, 1);
        }
        // 更新为非本系统订单
        if (!empty($notOurTrades)) {
            $this->refund->updateSyncStatus($notOurTrades, 3);
            unset($notOurTrades);
        }

        // 延迟 5 分钟转入
        if ($delayTrades) {
            if ($this->skipDelay($params)) { // 防止死循环
                throw new Exception('重复循环调用退单转入');
            }

            $targetTrades = array_unique(array_values($delayTrades));
            $targetRefunds = array_unique(array_keys($delayTrades));
            // 下载订单信息
            dispatch((new TradeBatchDownloadJob(['tids' => $targetTrades, 'platform' => 'taobao', 'key' => 'refund_delay']))
                         ->chain([
                                     new TaobaoTradeBatchTransferJob(['tids' => $targetTrades, 'key' => 'refund_transfer_after_trade']),
                                     new RefundBatchTransferJob(['refund_ids' => $targetRefunds, 'key' => 'refund_transfer_after_trade', 'from' => 'delay']) // 增加标识，防止一直循环
                                 ]));

            // 延迟触发退单转入
            // dispatch((new RefundBatchTransferJob(['refund_ids' => array_keys($delayTrades), 'from' => 'delay', 'key' => 'refundBatchTransfer:trade-no-found']))->delay(300));
            // 执行转入相关任务
            // dispatch((new RefundDelayTransferJob(['delay_trades' => $delayTrades, 'key' => 'refundBatchTransfer:trade-no-found']))->delay(60));
        }

        return true;
    }

    /**
     * 发起标识是否循环发起
     *
     * @param $params
     * @return bool
     *
     * @author linqihai
     * @since 2020/3/25 14:10
     */
    protected function skipDelay($params)
    {
        return isset($params['from']) && 'delay' == $params['from'];
    }

    private function batchInsertStdRefunds($insertRefunds)
    {
        $stdRefunds = $stdRefundItems = [];
        foreach ($insertRefunds as $formatStdRefund) {
            $stdRefunds[] = $formatStdRefund['refund'];
            if (!empty($formatStdRefund['items'])) {
                $stdRefundItems = array_merge($stdRefundItems, $formatStdRefund['items']);
            }
        }
        try {
            DB::beginTransaction();
            $this->insertMulti('sys_std_refund', $stdRefunds);
            if (!empty($stdRefundItems)) {
                $this->insertMulti('sys_std_refund_item', $stdRefundItems);
            }
            // 更新为已转入
            $this->refund->updateSyncStatus(array_keys($insertRefunds), 1);

            Event::dispatch(new TaobaoRefundCreateEvent($stdRefunds));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('create std refund fail' . $e->getMessage());

            return false;
        }

        return true;
    }
}
