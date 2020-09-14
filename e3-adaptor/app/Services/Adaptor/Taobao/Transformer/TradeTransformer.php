<?php


namespace App\Services\Adaptor\Taobao\Transformer;


use App\Models\Sys\Shop;
use App\Models\SysStdTrade;
use App\Models\SysStdTradeItem;
use App\Models\SysStdTradePromotion;
use App\Models\TaobaoTrade;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Taobao\Events\TaobaoTradeCreateEvent;
use App\Services\Adaptor\Taobao\Events\TaobaoTradeUpdateEvent;
use App\Services\Adaptor\Taobao\Repository\TaobaoTradeRepository;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Log;

class TradeTransformer implements TransformerContract
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

    public function __construct(TaobaoTradeRepository $trade)
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
     * @author linqihai
     * @since 2020/1/10 15:19
     */
    public function transfer($params)
    {
        if (empty($params['tid'])) {
            throw new InvalidArgumentException('tid required!');
        }

        $shopCode = $params['shop_code'] ?? '';
        $tid = $params['tid'];
        $taobaoTrade = TaobaoTrade::find($tid);
        if (empty($taobaoTrade)){
            return false;
        }
        if (empty($shopCode)) {
            $shop = Shop::select('code')->where('seller_nick', $taobaoTrade['seller_nick'])->first();
            $shopCode = $shop['code'];
        }
        if (empty($shopCode)) {
            throw new Exception('店铺不存在' . $taobaoTrade['seller_nick']);
        }
        $this->shopCode = $shopCode;
        $existTrade = SysStdTrade::where(['platform' => self::PLATFORM, 'tid' => $tid])->first();
        if ($existTrade && isset($taobaoTrade['modified']) && !empty($taobaoTrade['modified'])
            && strtotime($existTrade['modified']) >= strtotime($taobaoTrade['modified'])) {
            return $taobaoTrade->fill(['sync_status' => 1])->save();
        }
        $formatStdTrade = $this->format($taobaoTrade);
        if ($existTrade) {
            $result = $this->updateStdTrade($formatStdTrade, $existTrade);
        } else {
            $result = $this->createStdTrade($formatStdTrade);
        }

        // 更新转入状态
        $result = $taobaoTrade->fill(['sync_status' => $result ? 1 : 2])->save();

        return true;
    }

    /**
     * 更新
     * @param $formatStdTrade
     * @param $existTrade
     * @return bool
     *
     * @author linqihai
     * @since 2019/12/19 21:23
     */
    protected function updateStdTrade($formatStdTrade, $existTrade)
    {
        $origin = $existTrade->toArray();
        try {
            DB::beginTransaction();
            unset($formatStdTrade['trade']['created_at']);
            $existTrade->fill($formatStdTrade['trade'])->save();

            $formatItems = $formatStdTrade['items'];
            if ($formatItems) {
                foreach ($formatItems as $formatItem) {
                    // 不需要更新的字段
                    unset($formatItem['row_index'], $formatItem['created_at'], $formatItem['updated_at']);
                    $where = ['platform' => self::PLATFORM, 'tid' => $formatStdTrade['trade']['tid'], 'oid' => $formatItem['oid']];
                    SysStdTradeItem::where($where)->update($formatItem);
                }
            }

            /*if ($formatStdTrade['promotions']) {
                SysStdTradePromotion::where(['platform' => self::PLATFORM])->where('tid', $formatStdTrade['trade']['tid'])->delete();
                $this->insertMulti('sys_std_trade_promotion', $formatStdTrade['promotions']);
            }*/
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('update std trade fail' . $e->getMessage());
            return false;
        }
        // 更新事件
        Event::dispatch(new TaobaoTradeUpdateEvent($formatStdTrade['trade'], $origin));
        unset($origin);

        return true;
    }

    /**
     * 新增
     * @param $formatStdTrade
     * @return bool
     *
     * @author linqihai
     * @since 2019/12/20 10:05
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
            Event::dispatch(new TaobaoTradeCreateEvent([$formatStdTrade['trade']]));
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
     * @param $taobaoTrade
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 20:48
     */
    public function format($taobaoTrade)
    {
        $originContent = data_get($taobaoTrade, 'origin_content');
        if (!is_array($originContent)) {
            $originTrade = json_decode($originContent, true);
        } else {
            $originTrade = $originContent;
        }
        $originTrade = data_get($originTrade, 'trade_fullinfo_get_response.trade', []);
        $trade = $this->formatTrade($originTrade);
        $items = $this->formatTradeItems(data_get($originTrade, 'orders.order', []), $trade);
        $promotions = $this->formatTradePromotions($originTrade);

        return compact('trade', 'items', 'promotions');
    }

    /**
     * 格式化 trade
     *
     * @param $trade
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 20:49
     */
    public function formatTrade($trade)
    {
        if (empty($trade)) {
            return [];
        }
        $now = Carbon::now()->toDateTimeString();
        return [
            'platform'          => self::PLATFORM,
            'tid'               => $trade['tid'],
            'shop_code'         => $this->shopCode,
            'total_fee'         => $trade['total_fee'] ?? 0,
            'discount_fee'      => $trade['discount_fee'] ?? 0,
            'coupon_fee'        => $trade['coupon_fee'] ?? 0,
            'pay_no'            => $trade['alipay_no'] ?? 0,
            'payment'           => $trade['payment'] ?? 0,
            'pay_type'          => $trade['pay_type'] ?? 'alipay',
            'pay_status'        => $trade['pay_status'] ?? 'paid',
            'post_fee'          => $trade['post_fee'] ?? 0,
            'receiver_name'     => $trade['receiver_name'] ?? '',
            'receiver_country'  => $trade['receiver_country'] ?? '中国',
            'receiver_state'    => $trade['receiver_state'] ?? '',
            'receiver_city'     => $trade['receiver_city'] ?? '',
            'receiver_district' => $trade['receiver_district'] ?? '',
            'receiver_town'     => $trade['receiver_town'] ?? '',
            'receiver_address'  => $trade['receiver_address'] ?? '',
            'receiver_zip'      => $trade['receiver_zip'] ?? '',
            'receiver_mobile'   => $trade['receiver_mobile'] ?? '',
            'receiver_phone'    => $trade['receiver_phone'] ?? '',
            'buyer_email'       => $trade['buyer_email'] ?? '',
            'status'            => $trade['status'] ?? '',
            'type'              => $trade['type'] ?? 'fixed',
            'buyer_nick'        => $trade['buyer_nick'] ?? '',
            'seller_flag'       => $trade['seller_flag'] ?? '',
            'seller_memo'       => $trade['seller_memo'] ?? '',
            'buyer_message'     => $trade['buyer_message'] ?? '',
            'step_trade_status' => $trade['step_trade_status'] ?? 0,
            'step_paid_fee'     => $trade['step_paid_fee'] ?? 0,
            'pay_time'          => $trade['pay_time'] ?? 0,
            'created'           => $trade['created'] ?? 0,
            'modified'          => $trade['modified'] ?? 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];
    }

    /**
     * 格式化 items
     * @param $items
     * @param $trade
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 21:03
     */
    public function formatTradeItems($items, $trade)
    {
        $now = Carbon::now()->toDateTimeString();
        $formatItems = [];
        foreach ($items as $key => $item) {
            // 赠品金额及优惠设置为 0 -- 优惠券商品，没有 outer_iid
            if (isset($item['outer_iid']) && Str::contains($item['outer_iid'], ['GWP'])) {
                $item['price'] = 0;
                $item['discount_fee'] = 0;
            }
            $colorSize = $this->parseColorSize($item['sku_properties_name'] ?? '');
            $format = [
                'platform'          => self::PLATFORM,
                'tid'               => $trade['tid'],
                'row_index'         => $key + 1,
                'oid'               => $item['oid'] ?? '',
                'title'             => $this->parseTitle($item['title']),
                'sku_id'            => $item['sku_id'] ?? '',
                'num_iid'           => $item['num_iid'] ?? '',
                'outer_iid'         => $item['outer_iid'] ?? 0,
                'outer_sku_id'      => $item['outer_sku_id'] ?? 0,
                'num'               => $item['num'],
                'price'             => $item['price'] ?? 0,
                'total_fee'         => $item['total_fee'] ?? 0,
                'discount_fee'      => $item['discount_fee'] ?? 0,
                'adjust_fee'        => $item['adjust_fee'] ?? 0,
                'part_mjz_discount' => $item['part_mjz_discount'] ?? 0,
                'payment'           => $item['payment'] ?? 0,
                'divide_order_fee'  => $item['divide_order_fee'] ?? 0,
                'color'             => $colorSize['color'] ?? 0,
                'size'              => $colorSize['size'] ?? 0,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
            $formatItems[] = $format;
        }

        return $formatItems;
    }

    /**
     * 格式化 promotions
     *
     * @param $promotions
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 21:07
     */
    public function formatTradePromotions($originTrade)
    {
        $promotions = data_get($originTrade, 'promotion_details.promotion_detail', []);
        $formatItems = [];
        if (empty($promotions)){
            return $formatItems;
        }
        $now = Carbon::now()->toDateTimeString();
        foreach ($promotions as $key => $item) {
            if (empty($item)) {
                continue;
            }
            $format = [
                'platform'       => self::PLATFORM,
                'tid'            => $originTrade['tid'],
                'id'             => $item['id'],
                'promotion_id'   => $item['promotion_id'],
                'promotion_name' => $item['promotion_name'] ?? '',
                'promotion_desc' => $item['promotion_desc'] ?? '',
                'discount_fee'   => $item['discount_fee'] ?? 0,
                'gift_item_id'   => $item['gift_item_id'] ?? 0,
                'gift_item_num'  => $item['gift_item_num'] ?? 0,
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
     *
     * @author linqihai
     * @since 2020/3/23 17:25
     */
    public function parseColorSize($propertiesName)
    {
        $color = $size = '';
        $properties = explode(';', $propertiesName);
        if (isset($properties[0]) && !empty($properties[0])) {
            $colorArr = explode(':', $properties[0]);
            $color = array_pop($colorArr); // 取最后一个
        }
        if (isset($properties[1]) && !empty($properties[1])) {
            $sizeArr = explode(':', $properties[1]);
            $size = array_pop($sizeArr);
        }

        return compact('color', 'size');
    }

    /**
     * 重新组织商品名称
     *
     * @param $title
     * @return bool|string
     *
     * @author linqihai
     * @since 2020/3/23 20:32
     */
    public function parseTitle($title)
    {
        $needle = '阿迪达斯';
        if ($pos = strpos($title, $needle)) {
            $title = trim(substr($title, $pos));
        }

        return $title;
    }
}
