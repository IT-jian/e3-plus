<?php


namespace App\Services\Adaptor\Taobao\Transformer;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Models\SysStdTrade;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Taobao\Events\TaobaoTradeCreateEvent;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeTransferJob;
use App\Services\Adaptor\Taobao\Repository\TaobaoTradeRepository;
use DB;
use Event;
use Exception;
use InvalidArgumentException;
use Log;

class TradeBatchTransformer extends TradeTransformer implements TransformerContract
{
    const PLATFORM = 'taobao';

    /**
     * @var string
     */
    protected $shopCode;

    /**
     * @var TaobaoTradeRepository
     */
    protected $trade;

    /**
     * 单个转入
     *
     * @param $params
     * @return bool
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/10 15:19
     */
    public function transfer($params)
    {
        if (empty($params['tids'])) {
            throw new InvalidArgumentException('tids required!');
        }
        $where = [];
        $tids = $params['tids'];
        $where[] = ['tid', 'IN', $tids];
        $taobaoTrades = $this->trade->getAll($where);
        if (empty($taobaoTrades)) {
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

        $existTrades = SysStdTrade::where(['platform' => self::PLATFORM])->whereIn('tid', $tids)->get();
        if (!$existTrades->isEmpty()) {
            $existTrades = $existTrades->keyBy('tid');
        }

        $updateTrades = $insertTrades = $notOurTrades = $skipTrades = [];
        $updateSuccess = [];
        foreach ($taobaoTrades as $taobaoTrade) {
            $this->shopCode = $sellerNickShopCodeMap[$taobaoTrade->seller_nick] ?? null;
            if (empty($this->shopCode)) {
                $notOurTrades[] = $taobaoTrade->tid;
                continue;
            }
            // 未付款的不再同步
            // PAY_PENDING(国际信用卡支付付款确认中) * WAIT_PRE_AUTH_CONFIRM(0元购合约中) * PAID_FORBID_CONSIGN(拼团中订单或者发货强管控的订单，已付款但禁止发货)
            if (in_array($taobaoTrade->status, ['WAIT_BUYER_PAY', 'TRADE_NO_CREATE_PAY', 'PAID_FORBID_CONSIGN', 'WAIT_PRE_AUTH_CONFIRM', 'PAY_PENDING'])) {
                if ('step' != $taobaoTrade->type) {
                    $skipTrades[] = $taobaoTrade->tid;
                    continue;
                }
            }
            if (!$existTrades->isEmpty() && isset($existTrades[$taobaoTrade->tid])) {
                if (isset($taobaoTrade->modified) && !empty($taobaoTrade->modified) && strtotime($existTrades[$taobaoTrade->tid]->modified) >= strtotime($taobaoTrade->modified)) {
                    $skipTrades[] = $taobaoTrade->tid;
                    continue;
                }
                try {
                    // 更新订单
                    $this->updateStdTrade($this->format($taobaoTrade), $existTrades[$taobaoTrade->tid]);
                    $updateSuccess[] = $taobaoTrade->tid;
                } catch (Exception $e) {
                    $updateTrades[$taobaoTrade->tid] = [
                        'shop_code' => $this->shopCode,
                        'tid'       => $taobaoTrade->tid,
                    ];
                }
            } else {
                $insertTrades[$taobaoTrade->tid] = $this->format($taobaoTrade);
            }
        }

        if ($updateSuccess) {
            $this->trade->updateSyncStatus($updateSuccess, 1);
        }

        // 批量插入
        if (!empty($insertTrades)) {
            $result = $this->batchInsertStdTrades($insertTrades);
            if (!$result) {
                throw new Exception('批量格式化淘宝订单失败');
            }
        }
        // 处理更新失败 推送到单个转入队列中处理
        foreach ($updateTrades as $updateTrade) {
            dispatch(new TaobaoTradeTransferJob(['tid' => $updateTrade['tid'], 'shop_code' => $updateTrade['shop_code']]));
        }
        if (!empty($skipTrades)) {
            $this->trade->updateSyncStatus($skipTrades);
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
     * @since 2019/12/20 10:05
     * @author linqihai
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

            Event::dispatch(new TaobaoTradeCreateEvent($stdTrades));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('create std trade fail' . $e->getMessage());

            return false;
        }

        return true;
    }
}
