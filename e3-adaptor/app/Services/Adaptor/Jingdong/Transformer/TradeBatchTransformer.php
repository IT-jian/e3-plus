<?php


namespace App\Services\Adaptor\Jingdong\Transformer;


use App\Models\Sys\Shop;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Jingdong\Events\JingdongTradeCreateEvent;
use App\Services\Adaptor\Jingdong\Jobs\JingdongTradeTransferJob;
use App\Services\Adaptor\Jingdong\Repository\JingdongTradeRepository;
use DB;
use Event;
use Exception;
use InvalidArgumentException;
use Log;

class TradeBatchTransformer extends TradeTransformer implements TransformerContract
{
    const PLATFORM = 'jingdong';

    /**
     * @var string
     */
    protected $shopCode;

    /**
     * @var JingdongTradeRepository
     */
    protected $trade;

    /**
     * 单个转入
     *
     * @param $params
     * @return bool
     * @throws Exception
     */
    public function transfer($params)
    {
        if (empty($params['order_ids'])) {
            throw new InvalidArgumentException('order_ids required!');
        }
        $where = [];
        $orderIds = $params['order_ids'];
        $where[] = ['order_id', 'IN', $orderIds];
        $jingdongTrades = $this->trade->getAll($where);
        if (empty($jingdongTrades)) {
            throw new InvalidArgumentException('RDS 订单数据不存在!');
        }

        $sellerNickShopCodeMap = [];
        $shops = Shop::available(self::PLATFORM)->get();
        foreach ($shops as $shop) {
            $sellerNickShopCodeMap[$shop['seller_nick']] = $shop['code'];
        }
        if (empty($sellerNickShopCodeMap)) {
            throw new Exception('店铺不存在，请添加店铺后执行');
        }

        $existTrades = SysStdTrade::where(['platform' => self::PLATFORM])->whereIn('tid', $orderIds)->get();
        if (!$existTrades->isEmpty()) {
            $existTrades = $existTrades->keyBy('tid');
        }

        $updateTrades = $insertTrades = $notOurTrades = [];
        $updateSuccess = [];
        foreach ($jingdongTrades as $jingdongTrade) {
            $this->shopCode = $sellerNickShopCodeMap[$jingdongTrade->vender_id] ?? null;
            if (empty($this->shopCode)) {
                $notOurTrades[] = $jingdongTrade->order_id;
                continue;
            }
            if (!$existTrades->isEmpty() && isset($existTrades[$jingdongTrade->order_id])) {
                /*if (strtotime($existTrades[$jingdongTrade->order_id]->modified) >= strtotime($jingdongTrade->modified)) {
                    continue;
                }*/
                try {
                    // 更新订单
                    $this->updateStdTrade($this->format($jingdongTrade), $existTrades[$jingdongTrade->order_id]);
                    $updateSuccess[] = $jingdongTrade->order_id;
                } catch (Exception $e) {
                    $updateTrades[$jingdongTrade->order_id] = [
                        'shop_code' => $this->shopCode,
                        'order_id'       => $jingdongTrade->order_id,
                    ];
                }
            } else {
                try {
                    $insertTrades[$jingdongTrade->order_id] = $this->format($jingdongTrade);
                } catch (Exception $e) {
                    info($e->getMessage());
                }
            }
        }

        if ($updateSuccess) {
            $this->trade->updateSyncStatus($updateSuccess, 1);
        }

        // 批量插入
        if (!empty($insertTrades)) {
            $result = $this->batchInsertStdTrades($insertTrades);
            if (!$result) {
                throw new Exception('批量格式化京东订单失败');
            }
        }
        // 更新的推送到 单个转入队列中处理
        foreach ($updateTrades as $updateTrade) {
            dispatch(new JingdongTradeTransferJob(['order_id' => $updateTrade['order_id'], 'shop_code' => $updateTrade['shop_code']]));
        }
        // 更新为非本系统订单
        if (!empty($notOurTrades)) {
            $this->trade->updateSyncStatus($notOurTrades, 3);
        }
        return true;
    }

    /**
     * 新增
     * @param $insertTrades
     * @return bool
     *
     * @throws Exception
     */
    private function batchInsertStdTrades($insertTrades)
    {
        $stdTrades = $stdTradeItems = $stdTradePromotions = [];
        foreach ($insertTrades as $formatStdTrade) {
            $stdTrades[] = $formatStdTrade['trade'];
            if (!empty($formatStdTrade['items'])) {
                $stdTradeItems = array_merge($stdTradeItems, $formatStdTrade['items']);
            }
            if (!empty($formatStdTrade['promotions'])) {
                $stdTradePromotions = array_merge($stdTradePromotions, $formatStdTrade['promotions']);
            }
        }
        try {
            DB::beginTransaction();
            $this->insertMulti('sys_std_trade', $stdTrades);
            if (!empty($stdTradeItems)) {
                $this->insertMulti('sys_std_trade_item', $stdTradeItems);
            }
            if (!empty($stdTradePromotions)) {
                $this->insertMulti('sys_std_trade_promotion', $stdTradePromotions);
            }

            $this->trade->updateSyncStatus(array_keys($insertTrades), 1);

            Event::dispatch(new JingdongTradeCreateEvent($stdTrades));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('create std trade fail' . $e->getMessage());

            return false;
        }

        return true;
    }
}
