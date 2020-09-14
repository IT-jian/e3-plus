<?php


namespace App\Services\Adaptor\Jingdong\Transformer;


use App\Facades\Adaptor;
use App\Models\JingdongOrderSplitAmount;
use App\Models\JingdongTrade;
use App\Models\Sys\Shop;
use App\Models\SysStdTrade;
use App\Models\SysStdTradeItem;
use App\Models\SysStdTradePromotion;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Jingdong\Events\JingdongTradeCreateEvent;
use App\Services\Adaptor\Jingdong\Events\JingdongTradeUpdateEvent;
use App\Services\Adaptor\Jingdong\Repository\JingdongTradeRepository;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Event;
use InvalidArgumentException;
use Log;

class TradeTransformer implements TransformerContract
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

    public function __construct(JingdongTradeRepository $trade)
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
        if (empty($params['order_id'])) {
            throw new InvalidArgumentException('order_id required!');
        }

        $shopCode = $params['shop_code'] ?? '';
        $orderId = $params['order_id'];
        $jingdongTrade = JingdongTrade::find($orderId);
        if (empty($jingdongTrade)){
            return false;
        }
        if (empty($shopCode)) {
            $shop = Shop::select('code')->where('seller_nick', $jingdongTrade['vender_id'])->first();
            $shopCode = $shop['code'];
        }
        if (empty($shopCode)) {
            throw new Exception('店铺不存在' . $jingdongTrade['vender_id']);
        }
        $this->shopCode = $shopCode;
        $existTrade = SysStdTrade::where(['platform' => self::PLATFORM, 'tid' => $orderId])->first();
        /*if (isset($existTrade['tid']) && $existTrade['modified'] == $jingdongTrade['modified']) { // 版本相同不处理
            return false;
        }*/
        $formatStdTrade = $this->format($jingdongTrade);
        if (isset($existTrade['tid'])) {
            $result = $this->updateStdTrade($formatStdTrade, $existTrade);
        } else {
            $result = $this->createStdTrade($formatStdTrade);
        }

        // 更新转入状态
        $result = $jingdongTrade->fill(['sync_status' => $result ? 1 : 2])->save();

        return true;
    }

    /**
     * 更新
     * @param $formatStdTrade
     * @param $existTrade
     * @return bool
     */
    protected function updateStdTrade($formatStdTrade, $existTrade)
    {
        $origin = $existTrade->toArray();
        try {
            DB::beginTransaction();
            $existTrade->fill($formatStdTrade['trade'])->save();

            $formatItems = $formatStdTrade['items'];
            if ($formatItems) {
                foreach ($formatItems as $formatItem) {
                    // 不需要更新的字段
                    unset($formatItem['row_index'], $formatItem['created_at'], $formatItem['updated_at']);
                    $where = ['platform' => self::PLATFORM, 'tid' => $formatStdTrade['trade']['tid'], 'sku_id' => $formatItem['sku_id']];
                    $item = SysStdTradeItem::where($where)->first();
                    $item->fill($formatItem);
                    if (!empty($item->getDirty())) {
                        $item->save();
                    } else {
                        // Log::info('not dirty', [$formatStdTrade['trade']['tid'], $item['sku_id']]);
                    }
                }
            }

            if ($formatStdTrade['promotions']) {
                $ids = array_column($formatStdTrade['promotions'], 'id');
                SysStdTradePromotion::where(['platform' => self::PLATFORM])->whereIn('id', $ids)->delete();
                $this->insertMulti('sys_std_trade_promotion', $formatStdTrade['promotions']);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('update std trade fail' . $e->getMessage());
            return false;
        }
        // 更新事件
        Event::dispatch(new JingdongTradeUpdateEvent($formatStdTrade['trade'], $origin));
        unset($origin);

        return true;
    }

    /**
     * 新增
     * @param $formatStdTrade
     * @return bool
     */
    protected function createStdTrade($formatStdTrade)
    {
        try {
            DB::beginTransaction();

            SysStdTrade::insert($formatStdTrade['trade']);
            if (!empty($formatStdTrade['items'])) {
                $this->insertMulti('sys_std_trade_item', $formatStdTrade['items']);
            }
            if (!empty($formatStdTrade['promotions'])) {
                $this->insertMulti('sys_std_trade_promotion', $formatStdTrade['promotions']);
            }
            // 转入事件
            Event::dispatch(new JingdongTradeCreateEvent([$formatStdTrade['trade']]));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('create std trade fail' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 格式化数据
     *
     * @param $jingdongTrade
     * @return array
     */
    public function format($jingdongTrade)
    {
        $originContent = data_get($jingdongTrade, 'origin_content');
        if (!is_array($originContent)) {
            $originTrade = json_decode($originContent, true);
        } else {
            $originTrade = $originContent;
        }
        $trade = $this->formatTrade($originTrade);
        $items = $this->formatTradeItems(data_get($originTrade, 'itemInfoList', []), $trade);
        $promotions = $this->formatTradePromotions($originTrade);
        unset($originTrade, $originContent);

        return compact('trade', 'items', 'promotions');
    }

    /**
     * 格式化 trade
     *
     * @param $trade
     * @return array
     */
    public function formatTrade($trade)
    {
        if (empty($trade)) {
            return [];
        }
        $discountFee = 0;
        $promotions = data_get($trade, 'couponDetailList', []);
        foreach ($promotions as $promotion) {
            if (empty($promotion)) {
                continue;
            }
            $discountFee += $promotion['couponPrice'];
        }
        $consigneeInfo = data_get($trade, 'consigneeInfo', []);
        if (in_array($consigneeInfo['province'], ['北京', '天津', '上海', '重庆'])) {
            $consigneeInfo['town'] = $consigneeInfo['county'];
            $consigneeInfo['county'] = $consigneeInfo['city'];
            $consigneeInfo['city'] = $consigneeInfo['province'] . '市';
        }
        $trade['balanceUsed'] = $trade['balanceUsed'] ?? 0;
        $trade['orderPayment'] = $trade['orderPayment'] ?? 0;
        $payment = $trade['balanceUsed'] + $trade['orderPayment'];
        $now = Carbon::now()->toDateTimeString();
        return [
            'platform'          => self::PLATFORM,
            'tid'               => $trade['orderId'],
            'shop_code'         => $this->shopCode,
            'total_fee'         => $trade['orderTotalPrice'] ?? 0, // 订单总金额。总金额=订单金额（不减优惠，不加运费服务费税费）
            'discount_fee'      => $discountFee ?? 0,
            'coupon_fee'        => 0,
            'pay_no'            => '',
            'payment'           => $payment, // 用户应付金额。应付款=货款-用户优惠-余额+运费+税费+服务费
            'pay_type'          => $trade['payType'],
            'pay_status'        => 'paid',
            'post_fee'          => $trade['freightPrice'] ?? 0,
            'receiver_name'     => $consigneeInfo['fullname'] ?? '',
            'receiver_country'  => $consigneeInfo['receiver_country'] ?? '中国',
            'receiver_state'    => $consigneeInfo['province'] ?? '',
            'receiver_city'     => $consigneeInfo['city'] ?? '',
            'receiver_district' => $consigneeInfo['county'] ?? '',
            'receiver_town'     => $consigneeInfo['town'] ?? '',
            'receiver_address'  => $consigneeInfo['fullAddress'] ?? '',
            'receiver_zip'      =>  '',
            'receiver_mobile'   => $consigneeInfo['mobile'] ?? '',
            'receiver_phone'    => $consigneeInfo['telephone'] ?? '',
            'buyer_email'       => data_get($trade, 'invoiceEasyInfo.invoiceConsigneeEmail', ''), // 取电子发票邮箱
            'status'            => $trade['orderState'] ?? '', // 枚举值：1）WAIT_SELLER_STOCK_OUT 等待出库 2）WAIT_GOODS_RECEIVE_CONFIRM 等待确认收货 3）WAIT_SELLER_DELIVERY等待发货（只适用于海外购商家，含义为“等待境内发货”标签下的订单,非海外购商家无需使用） 4) POP_ORDER_PAUSE POP暂停 5）FINISHED_L 完成 6）TRADE_CANCELED 取消 7）LOCKED 已锁定 8）WAIT_SEND_CODE 等待发码（LOC订单特有状态） 9）PAUSE 暂停（等待出库之前的状态） 10)DELIVERY_RETURN 配送退货 11）UN_KNOWN 未知 请联系运营
            'type'              => $trade['orderType'] ?? '22',
            'buyer_nick'        => $trade['pin'] ?? '',
            'seller_flag'       => '',
            'seller_memo'       => $trade['venderRemark'] ?? '',
            'buyer_message'     => $trade['orderRemark'] ?? '',
            'step_trade_status' => 0,
            'step_paid_fee'     => 0,
            'pay_time'          => $trade['paymentConfirmTime'] ?? 0,
            'created'           => $trade['orderStartTime'] ?? 0,
            'modified'          => $trade['modified'] ?? 0,
            'created_at'        => $now,
            'updated_at'        => $now,
            // 'fapiao_request' => isset($trade['invoiceEasyInfo']) && !empty($trade['invoiceEasyInfo']) ? 1 : 0,
        ];
    }

    /**
     * 格式化 items
     * @param $items
     * @param $trade
     * @return array
     */
    public function formatTradeItems($items, $trade)
    {
        $itemsPromotions = [];
        // 查询支付优惠信息
        $orderSplitAmount = JingdongOrderSplitAmount::where('order_id', $trade['tid'])->first();
        if (isset($orderSplitAmount['origin_content']) && !empty($orderSplitAmount['origin_content'])) {
            $orderItemsAmount = $orderSplitAmount->origin_content;
            $splitAmountOrderId = data_get($orderItemsAmount, '0.orderId');
            if ($splitAmountOrderId != $trade['tid']) {
                $orderSplitAmount = [];
            }
        }
        if (empty($orderSplitAmount)) {
            $params = ['shop_code' => $trade['shop_code'], 'order_id' => $trade['tid']];
            Adaptor::platform('jingdong')->download(AdaptorTypeEnum::JD_ORDER_SPLIT_AMOUNT, $params);
            $orderSplitAmount = JingdongOrderSplitAmount::where('order_id', $trade['tid'])->firstOrFail();
        }
        $orderItemsAmount = $orderSplitAmount->origin_content;
        // 获取商品 实际 payment
        foreach ($orderItemsAmount as $item) {
            $itemsPromotions[$item['skuId']] = $item;
        }
        // @todo 计算优惠金额等信息
        $now = Carbon::now()->toDateTimeString();
        $formatItems = [];
        foreach ($items as $key => $item) {
            $rowIndex = $key + 1;
            $price = $item['jdPrice'] ?? 0;
            $payment = $this->getItemPayment($itemsPromotions[$item['skuId']]);
            $format = [
                'platform'          => self::PLATFORM,
                'tid'               => $trade['tid'],
                'row_index'         => $rowIndex,
                'oid'               => $rowIndex,
                'title'             => $this->parseTitle($item['skuName']),
                'sku_id'            => $item['skuId'] ?? '',
                'num_iid'           => $item['wareId'] ?? '',
                'outer_iid'         => $item['productNo'] ?? '',
                'outer_sku_id'      => $item['outerSkuId'] ?? 0,
                'num'               => $item['itemTotal'],
                'price'             => $price,
                'total_fee'         => $price * $item['itemTotal'],
                'discount_fee'      => 0,
                'adjust_fee'        => 0,
                'part_mjz_discount' => 0,
                'payment'           => $payment,
                'divide_order_fee'  => $payment,
                'color'             => '',
                'size'              => '',
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            $formatItems[] = $format;
        }

        return $formatItems;
    }

    /**
     * @todo 处理优惠
     * 格式化 promotions
     *
     * @param $originTrade
     * @return array
     */
    public function formatTradePromotions($originTrade)
    {
        $promotions = data_get($originTrade, 'couponDetailList', []);
        $formatItems = [];
        if (empty($promotions)){
            return $formatItems;
        }
        $now = Carbon::now()->toDateTimeString();
        // skuId 京东sku编号。(只有30-单品促销优惠 才有skuId，其他时值为””)
        // couponType 优惠类型: 20-套装优惠, 28-闪团优惠, 29-团购优惠, 30-单品促销优惠, 34-手机红包, 35-满返满送(返现), 39-京豆优惠,41-京东券优惠, 52-礼品卡优惠,100-店铺优惠
        // couponPrice 优惠金额
        foreach ($promotions as $key => $item) {
            if (empty($item)) {
                continue;
            }
            $format = [
                'platform'       => self::PLATFORM,
                'tid'            => $item['orderId'],
                'id'             => 0,
                'promotion_id'   => $item['promotion_id'] ?? '',
                'promotion_name' => $item['couponType'] ?? '',
                'promotion_desc' => '',
                'discount_fee'   => $item['couponPrice'] ?? 0,
                'gift_item_id'   => 0,
                'gift_item_num'  => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
            $formatItems[] = $format;
        }
        return $formatItems;
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

        $query = 'INSERT INTO '.$table.'('.implode(',', $key_arr).') VALUES'.$sql_mx;

        return DB::insert($query);
    }


    /**
     * 解析颜色尺码
     * @param $propertiesName
     * @return array
     */
    public function parseColorSize($propertiesName)
    {
        $properties = explode(' ', $propertiesName);
        $size = array_pop($properties);
        $color = array_pop($properties);

        return compact('color', 'size');
    }

    /**
     * 重新组织商品名称
     *
     * @param $title
     * @return bool|string
     */
    public function parseTitle($title)
    {
        $titleArr = explode('如图', $title);
        return $titleArr[0] ?? $title;
    }

    /**
     * 计算 实际支付金额
     *
     * @param $orderSplitAmount
     * @return mixed
     */
    public function getItemPayment($orderSplitAmount)
    {
        $payment = $orderSplitAmount['amountPayable']; // 消费支付
        $payment += $orderSplitAmount['moneyBalance']; // 订单使用余额
        // 139 钱包余额支付
        $payment += $orderSplitAmount['giftCardDiscount']; // 礼品卡总优惠
        $payment += $orderSplitAmount['mobileDiscount']; // 手机红包
        $payment -= $orderSplitAmount['shopFee'];        // 运费

        foreach ($orderSplitAmount['amountExpands'] as $amountExpand) {
            if (in_array($amountExpand['type'], [139, 123, 128, 133, 138, 150, 151, 156])) { // 结算方为京东的
                $payment += $amountExpand['amount'];
            } else if (in_array($amountExpand['type'], [131, 413, 444, 448, 160])) { // 服务费相关
                $payment -= $amountExpand['amount'];
            }
        }

        return $payment;
    }
}
