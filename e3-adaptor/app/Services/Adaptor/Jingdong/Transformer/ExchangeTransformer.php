<?php


namespace App\Services\Adaptor\Jingdong\Transformer;


use App\Models\JingdongRefund;
use App\Models\Sys\Shop;
use App\Models\SysStdExchange;
use App\Models\SysStdExchangeItem;
use App\Models\SysStdPlatformSku;
use App\Models\SysStdTradeItem;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Jingdong\Api\ExchangeSku;
use App\Services\Adaptor\Jingdong\Events\JingdongExchangeCreateEvent;
use App\Services\Adaptor\Jingdong\Events\JingdongExchangeUpdateEvent;
use Event;
use Exception;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class ExchangeTransformer implements TransformerContract
{
    const PLATFORM = 'jingdong';

    /**
     * @var string
     */
    private $shopCode;

    /**
     * @param $params
     * @return bool
     * @throws Exception
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
        // 换货
        if (20 != $jingdongRefund['customer_expect']) {
            return true;
        }
        // 换货skuid
        if (empty($jingdongRefund['change_sku'])) {
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
        $exitExchange = SysStdExchange::lockForUpdate()->where(['platform' => self::PLATFORM, 'dispute_id' => $serviceId])->first();
        // 不比较了，快递更新 服务单不会更新内容，心塞
        $formatExchange = $this->format($jingdongRefund);
        if ($exitExchange) {
            $result = $this->updateStdExchange($formatExchange, $exitExchange);
        } else {
            $result = $this->createStdExchange($formatExchange);
        }

        // 更新转入状态
        $result = $jingdongRefund->fill(['sync_status' => $result ? 1 : 2])->save();
    }

    /**
     * @param $formatStdExchange
     * @param $existExchange
     * @return bool
     * @throws Exception
     */
    private function updateStdExchange($formatStdExchange, $existExchange)
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

        Event::dispatch(new JingdongExchangeUpdateEvent($exchange, $origin));
        unset($origin);

        return true;
    }

    /**
     * @param $formatStdExchange
     * @return bool
     * @throws Exception
     */
    private function createStdExchange($formatStdExchange)
    {
        try {
            \DB::beginTransaction();

            SysStdExchange::insert($formatStdExchange['exchange']);
            if (!empty($formatStdExchange['items'])) {
                $this->insert_multi('sys_std_exchange_item', $formatStdExchange['items']);
            }
            Event::dispatch(new JingdongExchangeCreateEvent([$formatStdExchange['exchange']]));
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            print_r($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 格式化数据
     *
     * @param $jingdongExchange
     * @return array
     */
    public function format($jingdongExchange)
    {
        $originContent = data_get($jingdongExchange, 'origin_content');
        if (!is_array($originContent)) {
            $originExchange = json_decode($originContent, true);
        } else {
            $originExchange = $originContent;
        }
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

        $afsContactInfo = data_get($exchange, 'afsContactInfo', []); // 卖家仓库地址
        $contactInfo = data_get($exchange, 'customerInfo.contactInfo', []); // 买家联系信息
        $returnWareAddress = data_get($exchange, 'returnWareAddress', []); // 寄回买家地址

        $freightMessage = data_get($exchange, 'freightMessage', []); // 下载的时候查询组装
        return [
            'platform'             => self::PLATFORM,
            'dispute_id'           => $exchange['serviceId'],
            'tid'                  => $exchange['orderId'],
            'shop_code'            => $this->shopCode,
            'status'               => $this->serviceStatusMap($exchange['serviceStatus']),
            'refund_phase'         => 'aftersale',
            'refund_version'       => Carbon::createFromTimestampMs($exchange['updateDate'])->timestamp,
            'buyer_name'           => $contactInfo['contactName'] ?? '',
            'buyer_address'        => $returnWareAddress['detailAddress'] ?? '',
            'buyer_phone'          => $contactInfo['contactMobile'] ?? '',
            'buyer_logistic_name'  => $freightMessage['expressCompany'] ?? '',
            'buyer_logistic_no'    => $freightMessage['expressCode'] ?? '',
            'seller_address'       => $afsContactInfo['detailAddress'] ?? '',
            'seller_logistic_name' =>  '',
            'seller_logistic_no'   => '',
            'created'         => Carbon::createFromTimestampMs($exchange['applyTime'])->toDateTimeString(),
            'modified'        => Carbon::createFromTimestampMs($exchange['updateDate'])->toDateTimeString(),
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
        $serviceBillDetail = data_get($exchange, 'serviceBillDetailList.0', []); // 退单明细
        $wareChangeWithApplyDTO = data_get($exchange, 'wareChangeWithApplyDTO', []); // 换单明细

        $now = Carbon::now()->toDateTimeString();
        $formatItems = [];
        $where = [
            'platform' => self::PLATFORM,
            'tid'      => $exchange['orderId'],
            'sku_id'   => $serviceBillDetail['skuId'],
        ];
        $tradeItem = SysStdTradeItem::where($where)->first();
        $exchangeItem = $this->getExchangeItem($wareChangeWithApplyDTO['changeWareSku']);
        $format = [
            'platform'            => self::PLATFORM,
            'dispute_id'          => $exchange['serviceId'],
            'row_index'           => $tradeItem['row_index'] ?? 0,
            'oid'                 => $tradeItem['oid'] ?? 0,
            'goods_status'        => '',
            'bought_sku'          => $serviceBillDetail['skuId'] ?? '',
            'exchange_num_iid'      => $exchangeItem['wareId'] ?? '',
            'exchange_title'        => $exchangeItem['wareTitle'] ?? '',
            'exchange_outer_iid'    => $exchangeItem['itemNum'] ?? '',
            'exchange_outer_sku_id' => $exchangeItem['outerId'] ?? '',
            'exchange_color'        => '',
            'exchange_size'         => '',
            'exchange_sku'        => $wareChangeWithApplyDTO['changeWareSku'] ?? '',
            'bought_num_iid'      => $tradeItem['num_iid'] ?? '',
            'bought_outer_iid'    => $tradeItem['outer_iid'] ?? '',
            'bought_outer_sku_id' => $tradeItem['outer_sku_id'] ?? '',
            'bought_color'        => $tradeItem['color'] ?? '',
            'bought_size'         => $tradeItem['size'] ?? '',
            'num'                 => $serviceBillDetail['wareNum'] ?? 0,
            'price'               => $serviceBillDetail['payPrice'] ?? 0,
            'reason'              => $exchange['applyReason'] ?? '',
            'desc'                => $exchange['applyReason'] ?? '',
            'created_at'          => $now,
            'updated_at'          => $now,
        ];
        $formatItems[] = $format;
        return $formatItems;
    }

    public function getExchangeItem($skuId)
    {
        $sku = $colorSize = [];
        // 先查询本地是有sku
        $fields = ['sku_id', 'num_iid', 'outer_iid', 'outer_id', 'title'];
        $sku = SysStdPlatformSku::select($fields)->where('sku_id', $skuId)->where('platform', 'jingdong')->where('shop_code', $this->shopCode)->first();
        if ($sku) {
            return [
                'wareId' => $sku['num_iid'],
                'wareTitle' => $sku['title'],
                'itemNum' => $sku['outer_iid'],
                'outerId' => $sku['outer_id'],
            ];
        }
        // 通过接口查询
        $shop = Shop::where('code', $this->shopCode)->first();
        $api = new ExchangeSku($shop);
        $sku = $api->find($skuId);
        /*if (isset($sku['properties_name'])) {
            // 解析颜色和尺码
            $colorSize = app(TradeTransformer::class)->parseColorSize($sku['properties_name']);
        }*/

        return array_merge($sku, $colorSize);
    }

    private function insert_multi($table, $row_arr)
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
        return \DB::insert($query);
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

        return $map[$serviceStatus];
    }

}
