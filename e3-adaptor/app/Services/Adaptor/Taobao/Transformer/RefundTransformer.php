<?php


namespace App\Services\Adaptor\Taobao\Transformer;


use App\Models\Sys\Shop;
use App\Models\SysStdExchange;
use App\Models\SysStdExchangeItem;
use App\Models\SysStdRefund;
use App\Models\SysStdRefundItem;
use App\Models\SysStdTradeItem;
use App\Models\TaobaoRefund;
use App\Models\TaobaoTrade;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Taobao\Events\TaobaoRefundCreateEvent;
use App\Services\Adaptor\Taobao\Events\TaobaoRefundUpdateEvent;
use App\Services\Adaptor\Taobao\Repository\TaobaoRefundRepository;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

class RefundTransformer implements TransformerContract
{
    const PLATFORM = 'taobao';

    /**
     * @var string
     */
    protected $shopCode;

    /**
     * @var TaobaoRefundRepository
     */
    protected $refund;

    public function __construct(TaobaoRefundRepository $refund)
    {
        $this->refund = $refund;
    }

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
        if (empty($params['refund_id'])) {
            throw new InvalidArgumentException('refund_id required!');
        }

        $shopCode = $params['shop_code'] ?? '';
        $refundId = $params['refund_id'];
        $taobaoRefund = TaobaoRefund::find($refundId);
        if (empty($taobaoRefund)){
            return false;
        }

        if (empty($shopCode)) {
            $shop = Shop::select('code')->where('seller_nick', $taobaoRefund['seller_nick'])->first();
            $shopCode = $shop['code'];
        }
        if (empty($shopCode)) {
            throw new Exception('店铺不存在' . $taobaoRefund['seller_nick']);
        }
        $this->shopCode = $shopCode;
        $existTrade = SysStdRefund::where(['platform' => self::PLATFORM, 'refund_id' => $refundId])->first();
        if (!empty($existTrade) && isset($taobaoRefund['modified']) && !empty($taobaoRefund['modified']) && strtotime($existTrade['modified']) >= strtotime($taobaoRefund['modified'])) {
            return $this->refund->updateSyncStatus([$refundId], 1);
        }
        $formatStdRefund = $this->format($taobaoRefund);
        if (!empty($existTrade)) {
            $result = $this->updateStdRefund($formatStdRefund, $existTrade);
        } else {
            $result = $this->createStdRefund($formatStdRefund);
        }
        $this->refund->updateSyncStatus([$refundId], $result ? 1 : 2);

        return true;
    }

    /**
     * 更新
     * @param $formatStdRefund
     * @param $existRefund
     * @return bool
     *
     * @author linqihai
     * @since 2019/12/19 21:23
     */
    protected function updateStdRefund($formatStdRefund, $existRefund)
    {
        $origin = $existRefund->toArray();
        try {
            DB::beginTransaction();
            $existRefund->fill($formatStdRefund['refund'])->save();

            if ($formatStdRefund['items']) {
                foreach ($formatStdRefund['items'] as $formatItem) {
                    // 不需要更新的字段
                    $where = ['platform' => self::PLATFORM, 'refund_id' => $formatStdRefund['refund']['refund_id'], 'oid' => $formatItem['oid']];
                    $item = SysStdRefundItem::where($where)->first();
                    if (!empty($item)) {
                        $item->fill($formatItem)->save();
                    } else {
                        SysStdRefundItem::insert($formatItem);
                    }
                    unset($item);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            \Log::error('update std refund fail' . $e->getMessage());
            return false;
        }

        // 更新事件
        Event::dispatch(new TaobaoRefundUpdateEvent($formatStdRefund['refund'], $origin));
        unset($origin);
        return true;
    }

    /**
     * 新增
     * @param $formatStdRefund
     * @return bool
     *
     * @author linqihai
     * @since 2019/12/20 10:05
     */
    private function createStdRefund($formatStdRefund)
    {
        try {
            DB::beginTransaction();

            SysStdRefund::insert($formatStdRefund['refund']);
            if (!empty($formatStdRefund['items'])) {
                $this->insertMulti('sys_std_refund_item', $formatStdRefund['items']);
            }
            // 转入事件
            Event::dispatch(new TaobaoRefundCreateEvent([$formatStdRefund['refund']]));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            \Log::info('create std refund fail' . $e->getMessage());
            return false;
        }


        return true;
    }

    /**
     * 格式化数据
     *
     * @param $taobaoRefund
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 20:48
     */
    public function format($taobaoRefund)
    {
        $originContent = data_get($taobaoRefund, 'origin_content');
        if (!is_array($originContent)) {
            $originTrade = json_decode($originContent, true);
        } else {
            $originTrade = $originContent;
        }
        $originTrade = data_get($originTrade, 'refund_get_response.refund', []);
        $refund = $this->formatRefund($originTrade);
        $items = $this->formatRefundItems($originTrade);

        unset($originContent, $originTrade);

        return compact('refund', 'items');
    }

    /**
     * 格式化 trade
     *
     * @param $refund
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 20:49
     */
    public function formatRefund($refund)
    {
        if (empty($refund)) {
            return [];
        }
        $now = Carbon::now()->toDateTimeString();
        return [
            'platform'        => self::PLATFORM,
            'refund_id'       => $refund['refund_id'],
            'tid'             => $refund['tid'],
            'oid'             => $refund['oid'],
            'shop_code'       => $this->shopCode,
            'status'          => $refund['status'] ?? '',
            'order_status'    => $refund['order_status'] ?? '',
            'refund_phase'    => $refund['refund_phase'] ?? '',
            'refund_version'  => $refund['refund_version'] ?? '',
            'refund_fee'      => $refund['refund_fee'] ?? 0,
            'company_name'    => $refund['company_name'] ?? '',
            'sid'             => $refund['sid'] ?? '',
            'has_good_return' => $refund['has_good_return'] == 'true' ? 1 : 0,
            'created'         => $refund['created'] ?? 0,
            'modified'        => $refund['modified'] ?? 0,
            'created_at'      => $now,
            'updated_at'      => $now,
        ];
    }

    /**
     * 格式化 items
     * @param $items
     * @param $refund
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 21:03
     */
    public function formatRefundItems($refund)
    {
        $now = Carbon::now()->toDateTimeString();
        $fields = ['*'];
        $where = ['platform' => self::PLATFORM, 'oid' => $refund['oid'], 'tid' => $refund['tid']];
        $tradeItem = SysStdTradeItem::select($fields)->where($where)->firstOrFail();
        $formatItems = [];
        // 重新计算退款数量，当前的num为商品数量
        $number = $this->parseRefundNumber($refund, $tradeItem);
        $rowIndex = $tradeItem['row_index'] ?? 1;
        // 获取退单实际的sku
        $refundItem = $this->parseActualRefundItem($refund, $tradeItem);
        $format = [
            'platform'     => self::PLATFORM,
            'refund_id'    => $refund['refund_id'],
            'row_index'    => $rowIndex,
            'oid'          => $refund['oid'] ?? $rowIndex,
            'num_iid'      => $refund['num_iid'] ?? '',
            'outer_iid'    => $refundItem['outer_iid'] ?? '',
            'outer_sku_id' => $refundItem['outer_sku_id'] ?? '',
            'sku_id'       => $refundItem['sku_id'] ?? '',
            'num'          => $number ?? 1,
            'refund_fee'   => $refund['refund_fee'] ?? 0, // 单个退款的，所以
            'reason'       => $refund['reason'] ?? '',
            'desc'         => $refund['desc'] ?? '',
            'created_at'   => $now,
            'updated_at'   => $now,
        ];
        $formatItems[] = $format;
        return $formatItems;
    }

    /**
     * 解析实际退款 sku
     *
     * @param $refund
     * @param $tradeItem
     * @return array
     */
    public function parseActualRefundItem($refund, $tradeItem)
    {
        if ($refund['has_good_return'] == 'true') { // 已成功的换货新单sku
            // 存在换货单，则取换货新单sku信息
            $exchangeItem = SysStdExchangeItem::where('oid', $refund['oid'])->orderBy('updated_at', 'desc')->first();
            if (!empty($exchangeItem) && $tradeItem['sku_id'] != $exchangeItem['exchange_sku']) {
                $exist = SysStdExchange::where('dispute_id', $exchangeItem['dispute_id'])->where('platform', self::PLATFORM)->where('status', 'SUCCESS')->exists();
                if ($exist) {
                    return [
                        'outer_iid'    => $exchangeItem['exchange_outer_iid'],
                        'outer_sku_id' => $exchangeItem['exchange_outer_sku_id'],
                        'sku_id'       => $exchangeItem['exchange_sku'],
                    ];
                }
            }
        }

        return $tradeItem;
    }

    protected function insertMulti($table, $row_arr)
    {
        $row_arr = array_values($row_arr);

        $sql_mx = '';
        $key_arr = array_keys($row_arr[0]);

        foreach ($row_arr as $row) {
            $sql_mx .= ",(";
            foreach($key_arr as $key){
                if(is_null($row[$key])){
                    $sql_mx .= "NULL,";
                }else{
                    $sql_mx .= "'".addslashes($row[$key])."',";
                }
            }
            $sql_mx = rtrim($sql_mx, ','). ')';
        }
        $sql_mx = substr($sql_mx, 1);

        $query = 'INSERT INTO '.$table.'(`'.implode('`,`', $key_arr).'`) VALUES'.$sql_mx;

        return DB::insert($query);
    }

    /**
     * 根据退款金额，计算退款数量
     *
     * @param $refund
     * @param $tradeItem
     * @return float|int
     *
     * @author linqihai
     * @since 2020/3/23 21:07
     */
    protected function parseRefundNumber($refund, $tradeItem)
    {
        if (1 != $refund['has_good_return']) {
            return $tradeItem['num'];
        }
        // 订单商品不存在
        if ($tradeItem && empty($tradeItem['divide_order_fee'])) {
            $trade = TaobaoTrade::where('tid', $tradeItem['tid'])->first();
            $items = data_get($trade, 'origin_content.trade_fullinfo_get_response.trade.orders.order', []);
            foreach ($items as $item) {
                if ($item['oid'] == $tradeItem['oid']) {
                    $tradeItem['divide_order_fee'] = $item['divide_order_fee'] ?? 0.00;
                }
            }
        }
        if (empty($tradeItem) || empty($tradeItem['num']) || empty($tradeItem['divide_order_fee'])) {
            return 1;
        }
        $number = 0;
        if ($tradeItem['num'] > 0 && $tradeItem['divide_order_fee'] > 0) {
            $number = $refund['refund_fee'] / ($tradeItem['divide_order_fee'] / $tradeItem['num']);
        }

        if ($number < 1) {
            $number = 1;
        } else {
            $number = ceil($number);
        }

        return $number;
    }
}
