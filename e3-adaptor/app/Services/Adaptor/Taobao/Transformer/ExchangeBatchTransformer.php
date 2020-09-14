<?php


namespace App\Services\Adaptor\Taobao\Transformer;


use App\Models\SysStdExchange;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Taobao\Events\TaobaoExchangeCreateEvent;
use App\Services\Adaptor\Taobao\Jobs\ExchangeTransferJob;
use DB;
use Event;
use Exception;
use Log;

class ExchangeBatchTransformer extends ExchangeTransformer implements TransformerContract
{
    /**
     * @param $params
     * @return bool
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/10 15:20
     */
    public function transfer($params)
    {
        if (empty($params['dispute_ids'])) {
            throw new \InvalidArgumentException('dispute_ids required!');
        }
        $shopCode = $params['shop_code'] ?? '';
        if (empty($shopCode)) {
            throw new \InvalidArgumentException('shop_code required!');
        }
        $disputeIds = $params['dispute_ids'];
        $where[] = ['dispute_id', 'IN', $disputeIds];
        $taobaoExchanges = $this->exchangeRepository->getAll($where);
        if (empty($taobaoExchanges)){
            return false;
        }
        $this->shopCode = $shopCode;
        $existExchanges = SysStdExchange::where('platform', self::PLATFORM)->whereIn('dispute_id', $disputeIds)->get();
        if (!$existExchanges->isEmpty()) {
            $existExchanges = $existExchanges->keyBy('dispute_id');
        }

        $updateExchanges = $insertExchanges = $skipExchanges = [];
        $updateSuccess = [];
        foreach ($taobaoExchanges as $taobaoExchange) {
            $disputeId = $taobaoExchange->dispute_id;
            if (!$existExchanges->isEmpty() && isset($existExchanges[$disputeId])) {
                if (isset($taobaoExchange->modified) && !empty($taobaoExchange->modified) && strtotime($existExchanges[$disputeId]->modified) >= strtotime($taobaoExchange->modified)) {
                    $skipExchanges[] = $disputeId;
                    continue;
                }
                try {
                    // 更新订单
                    $this->updateStdExchange($this->format($taobaoExchange), $existExchanges[$disputeId]);
                    $updateSuccess[] = $disputeId;
                } catch (Exception $e) {
                    $updateExchanges[$disputeId] = [
                        'shop_code' => $this->shopCode,
                        'dispute_id'       => $disputeId,
                    ];
                }
            } else {
                try {
                    $insertExchanges[$disputeId] = $this->format($taobaoExchange);
                } catch (Exception $e) {
                    $updateExchanges[$disputeId] = [
                        'shop_code' => $this->shopCode,
                        'dispute_id'       => $disputeId,
                    ];
                    Log::error("download [$disputeId] fail" . $e->getMessage());
                }
            }
        }
        if ($updateSuccess) {
            $this->exchangeRepository->updateSyncStatus($updateSuccess, 1);
        }
        // 批量插入
        if (!empty($insertExchanges)) {
            $result = $this->batchInsertStdExchanges($insertExchanges);
            if (!$result) {
                throw new Exception('批量格式化淘宝订单失败');
            }
        }

        if (!empty($skipExchanges)) {
            $this->exchangeRepository->updateSyncStatus($skipExchanges, 1);
        }
        // 更新失败的推送到 单个转入队列中处理
        foreach ($updateExchanges as $updateExchange) {
            dispatch(new ExchangeTransferJob(['dispute_id' => (string)$updateExchange['dispute_id'], 'shop_code' => $updateExchange['shop_code']]));
        }

        return true;
    }

    private function batchInsertStdExchanges($insertExchanges)
    {
        $stdExchanges = $stdExchangeItems = [];
        foreach ($insertExchanges as $formatStdExchange) {
            $stdExchanges[] = $formatStdExchange['exchange'];
            if (!empty($formatStdExchange['items'])) {
                $stdExchangeItems = array_merge($stdExchangeItems, $formatStdExchange['items']);
            }
        }
        try {
            DB::beginTransaction();
            $this->insertMulti('sys_std_exchange', $stdExchanges);
            if (!empty($stdExchangeItems)) {
                $this->insertMulti('sys_std_exchange_item', $stdExchangeItems);
            }

            $this->exchangeRepository->updateSyncStatus(array_keys($insertExchanges), 1);

            Event::dispatch(new TaobaoExchangeCreateEvent($stdExchanges));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('create std exchange fail' . $e->getMessage());

            return false;
        }

        return true;
    }
}
