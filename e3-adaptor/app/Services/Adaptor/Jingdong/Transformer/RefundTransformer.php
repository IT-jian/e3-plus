<?php


namespace App\Services\Adaptor\Jingdong\Transformer;


use App\Facades\Adaptor;
use App\Models\JingdongRefund;
use App\Models\Sys\Shop;
use App\Models\SysStdExchangeItem;
use App\Models\SysStdRefund;
use App\Models\SysStdRefundItem;
use App\Models\SysStdTrade;
use App\Models\SysStdTradeItem;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Jingdong\Events\JingdongRefundCreateEvent;
use App\Services\Adaptor\Jingdong\Events\JingdongRefundUpdateEvent;
use App\Services\Adaptor\Jingdong\Repository\JingdongRefundRepository;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Event;
use InvalidArgumentException;

class RefundTransformer implements TransformerContract
{
    const PLATFORM = 'jingdong';

    /**
     * @var string
     */
    protected $shopCode;

    /**
     * @var JingdongRefundRepository
     */
    protected $trade;

    public function __construct(JingdongRefundRepository $trade)
    {
        $this->trade = $trade;
    }

    /**
     * 单个转入
     *
     * @param $params
     * @return bool
     * @throws Exception
     *
     */
    public function transfer($params)
    {
        if (empty($params['service_id'])) {
            throw new InvalidArgumentException('service_id required!');
        }

        $shopCode = $params['shop_code'] ?? '';
        $serviceId = $params['service_id'];
        $jingdongRefund = JingdongRefund::find($serviceId);
        if (empty($jingdongRefund)){
            return false;
        }
        if (10 != $jingdongRefund['customer_expect']) {
            return true;
        }
        if (empty($shopCode)) {
            $shop = Shop::select('code')->where('seller_nick', $jingdongRefund['vender_id'])->first();
            $shopCode = $shop['code'];
        }
        if (empty($shopCode)) {
            throw new Exception('店铺不存在' . $jingdongRefund['vender_id']);
        }
        $this->shopCode = $shopCode;
        $existRefund = SysStdRefund::where(['platform' => self::PLATFORM, 'refund_id' => $serviceId])->first();
        // 不比较了，快递更新 服务单不会更新内容，心塞
        $formatRefund = $this->format($jingdongRefund);
        if ($existRefund) {
            $result = $this->updateStdRefund($formatRefund, $existRefund);
        } else {
            $result = $this->createStdRefund($formatRefund);
        }

        // 更新转入状态
        $result = $jingdongRefund->fill(['sync_status' => $result ? 1 : 2])->save();

        return true;
    }


    /**
     * @param $formatStdRefund
     * @param $existRefund
     * @return bool
     * @throws Exception
     */
    private function updateStdRefund($formatStdRefund, $existRefund)
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
                        unset($formatItem['row_index'], $formatItem['created_at'], $formatItem['updated_at']);
                        $item->fill($formatItem);
                        if (!empty($item->getDirty())) {
                            $item->save();
                        } else {
                            // \Log::info('not dirty', [$item['sku_id']]);
                        }
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
        Event::dispatch(new JingdongRefundUpdateEvent($formatStdRefund['refund'], $origin));
        unset($origin);
        return true;
    }

    /**
     * @param $formatStdRefund
     * @return bool
     * @throws Exception
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
            Event::dispatch(new JingdongRefundCreateEvent([$formatStdRefund['refund']]));
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
     * @param $jingdongRefund
     * @return array
     * @throws Exception
     */
    public function format($jingdongRefund)
    {
        $originContent = data_get($jingdongRefund, 'origin_content');
        if (!is_array($originContent)) {
            $originTrade = json_decode($originContent, true);
        } else {
            $originTrade = $originContent;
        }
        // 如果订单未下载，则通过API下载
        $exist = SysStdTrade::where('platform', 'jingdong')->where('tid', $originTrade['orderId'])->exists();
        if (!$exist) {
            $params = ['order_id' => $originTrade['orderId'], 'shop_code' => $this->shopCode];
            Adaptor::platform('jingdong')->download('tradeApi', $params);
            Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::TRADE, $params);
        }
        $refund = $this->formatRefund($originTrade);
        $items = $this->formatRefundItems($originTrade);
        unset($originContent, $originTrade);

        return compact('refund', 'items');
    }

    /**
     * @param $refund
     * @return array
     */
    public function formatRefund($refund)
    {
        if (empty($refund)) {
            return [];
        }
        $refundFee = 0;
        $serviceBillDetail = data_get($refund, 'serviceBillDetailList', []);
        foreach ($serviceBillDetail as $bill) {
            $refundFee += $bill['actualPayPrice'];
        }

        $now = Carbon::now()->toDateTimeString();
        $freightMessage = data_get($refund, 'freightMessage', []); // 下载的时候查询组装
        return [
            'platform'        => self::PLATFORM,
            'refund_id'       => $refund['serviceId'],
            'tid'             => $refund['orderId'],
            'oid'             => 0,
            'shop_code'       => $this->shopCode,
            'status'          => $this->serviceStatusMap($refund['serviceStatus']),
            'order_status'    => '', // 需要查询
            'refund_phase'    => 'aftersale',
            'refund_version'  => Carbon::createFromTimestampMs($refund['updateDate'])->timestamp,
            'refund_fee'      => $refundFee,
            'company_name'    => $freightMessage['expressCompany'] ?? '',
            'sid'             => $freightMessage['expressCode'] ?? '',
            'has_good_return' => 1,
            'created'         => Carbon::createFromTimestampMs($refund['applyTime'])->toDateTimeString(),
            'modified'        => Carbon::createFromTimestampMs($refund['updateDate'])->toDateTimeString(),
            'created_at'      => $now,
            'updated_at'      => $now,
        ];
    }

    public function serviceStatusMap($serviceStatus)
    {
        // WAIT_SELLER_AGREE(买家已经申请退款，等待卖家同意) WAIT_BUYER_RETURN_GOODS(卖家已经同意退款，等待买家退货) WAIT_SELLER_CONFIRM_GOODS(买家已经退货，等待卖家确认收货) SELLER_REFUSE_BUYER(卖家拒绝退款) CLOSED(退款关闭) SUCCESS(退款成功)
        // @see https://open.jd.com/home/home#/doc/common?listId=512
        $map = [
            '10001' => 'WAIT_SELLER_AGREE', // 待审核
            '10002' => '', // 待客户反馈
            '10012' => '', // 客户已反馈
            '10007' => '', // 已收货，待处理
            '7060' => '', // 原返取消，待处理
            '7023' => '', // 换新取消，待处理
            '7090' => '', // 线下换新取消，待处理
            '13000' => '', // 商家催收
            '10009' => 'WAIT_BUYER_CONFIRM_GOODS',
            '10005' => 'WAIT_SELLER_CONFIRM_GOODS', // 待收货
            '10004' => 'SELLER_REFUSE_BUYER', // 审核关闭
            '10011' => 'CLOSED', // 取消
            '10010' => 'SUCCESS', //  完成
        ];

        return $map[$serviceStatus] ?? '';
    }

    /**
     * @param $refund
     * @return array
     * @throws Exception
     */
    public function formatRefundItems($refund)
    {
        $now = Carbon::now()->toDateTimeString();
        $fields = ['*'];
        $where = ['platform' => self::PLATFORM, 'tid' => $refund['orderId']];
        $tradeItems = SysStdTradeItem::select($fields)->where($where)->get();
        if ($tradeItems->isEmpty()){
            throw new Exception('Trade items not found' . $refund['orderId']);
        }
        $tradeItems = $tradeItems->keyBy('sku_id');
        $originItems = data_get($refund, 'serviceBillDetailList', []);
        $formatItems = [];

        // 计算每个订单的退款金额
        $billMap = [];
        $serviceBillDetail = data_get($refund, 'serviceBillDetailList', []);
        foreach ($serviceBillDetail as $bill) {
            $billMap[$bill['skuId']] = $bill['actualPayPrice'];
        }
        foreach ($originItems as $originItem) {
            $tradeItem = $tradeItems[$originItem['skuId']] ?? [];
            if (empty($tradeItem)) {
                \Log::error('退货单转入异常:' . $refund['serviceId'], $originItem);
                throw new Exception($originItem['skuId'] . ' Refund Item Not Found In Trade Item ' . $refund['orderId']);
            }
            // 查询是否为换退的商品
            $tradeItem = $this->parseActualRefundItem($refund, $tradeItem);
            $refundFee = $billMap[$originItem['skuId']] ?? $tradeItem['payment'];
            $format = [
                'platform'     => self::PLATFORM,
                'refund_id'    => $refund['serviceId'],
                'row_index'    => $tradeItem['row_index'] ?? 1,
                'oid'          => 0,
                'num_iid'      => $tradeItem['num_iid'] ?? '',
                'outer_iid'    => $tradeItem['outer_iid'] ?? '',
                'outer_sku_id' => $tradeItem['outer_sku_id'] ?? '',
                'sku_id'       => $tradeItem['sku_id'] ?? '',
                'num'          => $originItem['wareNum'],
                'refund_fee'   => $refundFee,
                'reason'       => $refund['applyReason'] ?? '',
                'desc'         => $refund['applyReason'] ?? '',
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
            $formatItems[] = $format;
        }

        return $formatItems;
    }

    /**
     * 实际退货商品
     * @param $refund
     * @param $tradeItem
     * @return mixed
     */
    public function parseActualRefundItem($refund, $tradeItem)
    {
        // 存在换货单，则取换货新单sku信息
        $exchangeItem = SysStdExchangeItem::leftJoin('sys_std_exchange', function ($join) {
            $join->on('sys_std_exchange_item.dispute_id', '=', 'sys_std_exchange.dispute_id');
        })->where('sys_std_exchange.tid', $refund['orderId'])->whereIn('sys_std_exchange.status', ['WAIT_BUYER_CONFIRM_GOODS', 'SUCCESS'])->where('bought_sku', $tradeItem['sku_id'])->first();
        if ($exchangeItem) {
            // 更新退货信息为换货
            $tradeItem['num_iid'] = $exchangeItem['exchange_num_iid'];
            $tradeItem['outer_iid'] = $exchangeItem['exchange_outer_iid'];
            $tradeItem['outer_sku_id'] = $exchangeItem['exchange_outer_sku_id'];
            $tradeItem['sku_id'] = $exchangeItem['exchange_sku'];
            // $tradeItem['row_index'] = $exchangeItem['row_index'];
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
}
