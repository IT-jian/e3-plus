<?php


namespace App\Services\Adaptor\Taobao\Transformer;


use App\Models\Sys\Shop;
use App\Models\SysStdExchange;
use App\Models\SysStdExchangeItem;
use App\Models\SysStdPlatformSku;
use App\Models\SysStdTradeItem;
use App\Models\TaobaoExchange;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Taobao\Api\ExchangeSku;
use App\Services\Adaptor\Taobao\Events\TaobaoExchangeCreateEvent;
use App\Services\Adaptor\Taobao\Events\TaobaoExchangeUpdateEvent;
use App\Services\Adaptor\Taobao\Repository\TaobaoExchangeRepository;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

class ExchangeTransformer implements TransformerContract
{
    const PLATFORM = 'taobao';

    /**
     * @var string
     */
    protected $shopCode;


    /**
     * @var TaobaoExchangeRepository
     */
    protected $exchangeRepository;

    public function __construct(TaobaoExchangeRepository $exchangeRepository)
    {
        $this->exchangeRepository = $exchangeRepository;
    }

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
        if (empty($params['dispute_id'])) {
            throw new \InvalidArgumentException('dispute_id required!');
        }
        $shopCode = $params['shop_code'] ?? '';
        $disputeId = $params['dispute_id'];
        $taobaoExchange = TaobaoExchange::find($disputeId);
        if (empty($taobaoExchange)){
            return false;
        }
        if (empty($shopCode)) {
            $shop = Shop::select('code')->where('seller_nick', $taobaoExchange['seller_nick'])->first();
            $shopCode = $shop['code'];
        }
        if (empty($shopCode)) {
            throw new Exception('店铺不存在' . $taobaoExchange['seller_nick']);
        }
        $this->shopCode = $shopCode;
        $existExchange = SysStdExchange::lockForUpdate()->where(['platform' => self::PLATFORM, 'dispute_id' => $disputeId])->first();
        if (!empty($existExchange) && strtotime($existExchange['modified']) >= $taobaoExchange['origin_modified']) {
            // return false;
        }
        // 组织标准表数据
        $formatStdExchange = $this->format($taobaoExchange->toArray());
        if (!empty($existExchange)) {
            $this->updateStdExchange($formatStdExchange, $existExchange);
        } else {
            $this->createStdExchange($formatStdExchange);
        }
        $this->exchangeRepository->updateSyncStatus([$disputeId]);
        return true;
    }

    /**
     * @param $formatStdExchange
     * @param $existExchange
     * @return bool
     * @throws Exception
     *
     * @author linqihai
     * @since 2019/12/23 18:21
     */
    protected function updateStdExchange($formatStdExchange, $existExchange)
    {
        $origin = $existExchange->toArray();
        $exchange = $formatStdExchange['exchange'];
        $exchangeItems = $formatStdExchange['items'];
        try {
            \DB::beginTransaction();
            $existExchange->fill($exchange)->save();

            $formatItems = $exchangeItems;
            if ($formatItems) {
                foreach ($formatItems as $formatItem) {
                    // 不需要更新的字段
                    $where = ['platform' => $formatItem['platform'], 'dispute_id' => $formatItem['dispute_id'], 'oid' => $formatItem['oid'], 'bought_sku' => $formatItem['bought_sku']];
                    $item = SysStdExchangeItem::where($where)->first();
                    if (!empty($item)) {
                        unset($formatItem['row_index'], $formatItem['created_at'], $formatItem['updated_at']);
                        $item->fill($formatItem);
                        if (!empty($item->getDirty())) {
                            $item->save();
                        } else {
                            // \Log::info('not dirty', [$item['sku_id']]);
                        }
                    } else {
                        SysStdExchangeItem::insert($formatItem);
                    }
                }
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            \Log::info('update std exchange fail' . $e->getMessage());
            return false;
        }

        Event::dispatch(new TaobaoExchangeUpdateEvent($exchange, $origin));
        unset($origin);

        return true;
    }

    /**
     * @param $formatStdExchange
     * @return bool
     * @throws Exception
     *
     * @author linqihai
     * @since 2019/12/23 18:21
     */
    private function createStdExchange($formatStdExchange)
    {
        try {
            \DB::beginTransaction();

            SysStdExchange::insert($formatStdExchange['exchange']);
            if (!empty($formatStdExchange['items'])) {
                $this->insertMulti('sys_std_exchange_item', $formatStdExchange['items']);
            }
            Event::dispatch(new TaobaoExchangeCreateEvent([$formatStdExchange['exchange']]));
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            throw new Exception($e);
            return false;
        }
        return true;
    }

    /**
     * 格式化数据
     *
     * @param $taobaoExchange
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 20:48
     */
    public function format($taobaoExchange)
    {
        $originContent = data_get($taobaoExchange, 'origin_content');
        if (!is_array($originContent)) {
            $originExchange = json_decode($originContent, true);
        } else {
            $originExchange = $originContent;
        }
        $originExchange = data_get($originExchange, 'tmall_exchange_get_response.result.exchange', []);
        // 主表
        $exchange = $this->formatExchange($originExchange);
        // 明细表
        $items = $this->formatExchangeItems($originExchange);

        unset($originContent, $originExchange);

        return compact('exchange', 'items');
    }

    /**
     * 格式化 主表信息
     *
     * @param $exchange
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 20:49
     */
    public function formatExchange($exchange)
    {
        if (empty($exchange)) {
            return [];
        }
        $now = Carbon::now()->toDateTimeString();

        return [
            'platform'             => self::PLATFORM,
            'dispute_id'           => $exchange['dispute_id'],
            'tid'                  => $exchange['alipay_no'],
            'shop_code'            => $this->shopCode,
            'status'               => $this->exchangeStatusMap($exchange['status']),
            'refund_phase'         => $exchange['refund_phase'] ?? '',
            'refund_version'       => $exchange['refund_version'] ?? '',
            'buyer_name'           => $exchange['buyer_name'] ?? '',
            'buyer_address'        => $exchange['buyer_address'] ?? '',
            'buyer_phone'          => $exchange['buyer_phone'] ?? '',
            'buyer_logistic_name'  => $exchange['buyer_logistic_name'] ?? '',
            'buyer_logistic_no'    => $exchange['buyer_logistic_no'] ?? '',
            'seller_address'       => $exchange['address'] ?? '',
            'seller_logistic_name' => $exchange['seller_logistic_name'] ?? '',
            'seller_logistic_no'   => $exchange['seller_logistic_no'] ?? '',
            'created'              => $exchange['created'] ?? 0,
            'modified'             => $exchange['modified'] ?? 0,
            'created_at'   => $now,
            'updated_at'   => $now,
        ];
    }

    /**
     * 格式化 字表信息
     * @param $exchange
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 21:03
     */
    public function formatExchangeItems($exchange)
    {
        $now = Carbon::now()->toDateTimeString();
        $formatItems = [];
        $where = [
            'platform' => self::PLATFORM,
            'tid'      => $exchange['alipay_no'],
            'oid'   => $exchange['biz_order_id'],
        ];
        $tradeItem = SysStdTradeItem::where($where)->firstOrFail();
        $exchangeItem = $this->getExchangeItem($exchange['exchange_sku']);
        $format = [
            'platform'              => self::PLATFORM,
            'dispute_id'            => $exchange['dispute_id'],
            'row_index'             => $tradeItem['row_index'] ?? 0,
            'oid'                   => $tradeItem['oid'] ?? 0,
            'goods_status'          => $exchange['good_status'] ?? '',
            'bought_sku'            => $exchange['bought_sku'] ?? '',
            'exchange_title'        => $exchangeItem['title'] ?? '',
            'exchange_outer_iid'    => $exchangeItem['outer_iid'] ?? '',
            'exchange_num_iid'      => $exchangeItem['num_iid'] ?? '',
            'exchange_outer_sku_id' => $exchangeItem['outer_id'] ?? '',
            'exchange_color'        => $exchangeItem['color'] ?? '',
            'exchange_size'         => $exchangeItem['size'] ?? '',
            'exchange_sku'          => $exchange['exchange_sku'] ?? '',
            'bought_num_iid'        => $tradeItem['num_iid'] ?? '',
            'bought_outer_iid'      => $tradeItem['outer_iid'] ?? '',
            'bought_outer_sku_id'   => $tradeItem['outer_sku_id'] ?? '',
            'bought_color'          => $tradeItem['color'] ?? '',
            'bought_size'           => $tradeItem['size'] ?? '',
            'num'                   => $exchange['num'] ?? 0,
            'price'                 => $exchange['price'] ?? 0,
            'reason'                => $exchange['reason'] ?? '',
            'desc'                  => $exchange['desc'] ?? '',
            'created_at'            => $now,
            'updated_at'            => $now,
        ];
        $formatItems[] = $format;
        return $formatItems;
    }

    public function getExchangeItem($skuId)
    {
        // 通过接口查询
        $shop = Shop::where('code', $this->shopCode)->first();
        // 查询本地sku
        $fields = [
            'title', 'outer_iid', 'num_iid', 'outer_id', 'color', 'size',
        ];
        $taobaoSku = SysStdPlatformSku::where('sku_id', $skuId)->where('platform', 'taobao')->where('shop_code', $this->shopCode)->first($fields);
        if (!empty($taobaoSku)) {
            return $taobaoSku->toArray();
        }
        $api = new ExchangeSku($shop);
        $sku = $api->find($skuId);
        // 解析颜色和尺码
        $colorSize = app(TradeTransformer::class)->parseColorSize($sku['properties_name']);

        return array_merge($sku, $colorSize);
    }

    public function exchangeStatusMap($status)
    {
        $statusMap = [
            '换货待处理'     => 'WAIT_SELLER_AGREE', // 买家已经申请换货，等待卖家同意换货申请
            '待买家退货'     => 'WAIT_BUYER_RETURN_GOODS', // 卖家已经同意换货，等待买家退货
            '买家已退货，待收货' => 'WAIT_SELLER_CONFIRM_GOODS', // 买家已经退货，等待卖家确认收货
            '换货关闭'      => 'CLOSED',
            '换货成功'      => 'SUCCESS',
            '待买家修改'     => 'WAIT_BUYER_MODIFY', // 卖家拒绝确认收货，等待买家修改换货申请
            '待发出换货商品'   => 'WAIT_SELLER_SEND_GOODS', // 卖家确认收货，等待卖家发货
            '待买家收货'     => 'WAIT_BUYER_CONFIRM_GOODS',
            '换货单关闭,请退款' => 'EXCHANGE_TRANSFER_REFUND',
            '请退款' => 'EXCHANGE_TRANSFER_REFUND', // 换货关闭，转退货退款
        ];

        return $statusMap[$status] ?? 'WAIT_SELLER_AGREE';
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
        foreach ($key_arr as $k => $key) {
            if ($key == 'desc') {
                $key_arr[$k] = '`desc`';
            }
        }
        $query = 'INSERT INTO '.$table.'('.implode(',', $key_arr).') VALUES'.$sql_mx;

        return DB::insert($query);
    }
}
