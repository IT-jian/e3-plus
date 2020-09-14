<?php
namespace App\Tasks;

use App\Models\Shangdian;
use App\Models\TaobaoTrade;
use Exception;
use Hhxsv5\LaravelS\Swoole\Task\Task;

class TaobaoTradeDownBatchTask extends Task
{
    private $data;
    private $result;
    private $startAt;
    private $logAt;
    private $logs;
    private $failTidArr;

    public function __construct($data)
    {
        $this->data = $data;
    }

    private function getTradeFields()
    {
        return [
            'end_time', 'buyer_message', 'shipping_type', 'buyer_cod_fee', 'seller_cod_fee ', 'express_agency_fee',
            'alipay_warn_msg', 'status', 'buyer_memo', 'seller_memo', 'modified', 'buyer_flag', 'seller_flag',
            'seller_nick', 'buyer_nick', 'title', 'type', 'created', 'iid', 'price', 'pic_path', 'num',
            'tid', 'alipay_no', 'payment', 'discount_fee', 'adjust_fee', 'snapshot_url', 'snapshot', 'seller_rate',
            'buyer_rate', 'trade_memo', 'pay_time', 'buyer_obtain_point_fee', 'point_fee', 'real_point_fee',
            'total_fee', 'post_fee', 'buyer_alipay_no',
            'receiver_name', 'receiver_state', 'receiver_city', 'receiver_district', 'receiver_address', 'receiver_zip',
            'receiver_mobile', 'receiver_phone', 'consign_time', 'buyer_email', 'commission_fee',
            'seller_alipay_no', 'seller_mobile', 'seller_phone', 'seller_name', 'seller_email',
            'available_confirm_fee', 'has_post_fee', 'received_payment',
            'cod_fee', 'cod_status', 'timeout_action_time', 'is_3D', 'num_iid', 'promotion', 'invoice_name', 'alipay_url',
            'orders', 'promotion_details', 'is_lgtype', 'is_brand_sale', 'is_force_wlb', 'trade_from', 'invoice_kind',
            'step_trade_status', 'step_paid_fee', 'mark_desc', 'o2o', 'o2o_guide_id', 'o2o_shop_id', 'o2o_guide_name', 'o2o_shop_name',
            'o2o_delivery', 'o2o_out_trade_id', 'omnichannel_param', 'omni_attr', 'omni_param', 'et_shop_id', 'service_orders',
            'est_con_time', 'buyerTaxNo', 'cnService', 'pushTime', 'store_code', 'logistics_company', 'is_sh_ship', 'shipper',
            'esDate', 'esRange', 'osDate', 'osRange', 'tmallDelivery', '3plTiming', 'cutoffMinutes', 'esTime', 'deliveryTime',
            'collectTime', 'sendTime', 'signTime', 'deliveryCps', 'gatherLastCenter', 'gatherStation', 'sorted', 'sort_info',
            'platform_subsidy_fee', 'paid_coupon_fee', 'coupon_fee', 'recharge_fee',
        ];
    }

    private function getTradeItemFields()
    {
        return [
            'adjust_fee', 'buyer_rate', 'cid', 'customization', 'discount_fee', 'is_daixiao', 'is_oversold', 'num',
            'num_iid', 'oid', 'order_from', 'outer_iid', 'outer_sku_id', 'payment', 'pic_path',
            'price', 'refund_status', 'seller_rate', 'seller_type', 'sku_id', 'sku_properties_name',
            'snapshot_url', 'status', 'title', 'total_fee', 'shop_id', 'tid', 'part_mjz_discount', 'zhengji_status',
            'estimate_con_time', 'store_code', 'sort_info', 'divide_order_fee',
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->startAt = microtime(true);
        $this->logAt = microtime(true);
        $this->failTidArr = [];
        $tid_arr = $this->data;

        $taobaoTradeFields = ['ttid', 'tid', 'is_tran_success', 'order_id', 'step_trade_status', 'modified'];
        $existTradeMap = TaobaoTrade::select($taobaoTradeFields)->whereIn('tid', $tid_arr)->get()->keyBy('tid')->toArray();

        $current = microtime(true);
        // \Log::info('select taobao trade', [($current - $this->logAt) * 1000, microtime(true)]);
        $this->logAt = $current;

        $tradeCloudMap = \App\Models\TaobaoCloudTrade::whereIn('tid', $tid_arr)->get();

        $current = microtime(true);
        // \Log::info('select rds trade', [(microtime(true) - $this->logAt) * 1000, microtime(true)]);
        $this->logAt = $current;

        $shangdianMap = $this->getShangdianAppNickMap();

        $current = microtime(true);
        // \Log::info('shangdian map', [(microtime(true) - $this->logAt) * 1000, microtime(true)]);
        $this->logAt = $current;

        foreach ($tradeCloudMap as $tradeCloud) {
            // $this->startAt = microtime(true);
            $this->logs = [];
            $this->log_cost('rds trade transfer begin');
            $tid = $tradeCloud['tid'];
            $taobaoTrade = $existTradeMap[$tid] ?? [];
            /*// 最后更新时间，没变则返回成功
            if (!empty($taobaoTrade) && !empty($taobaoTrade['modified']) && strtotime($taobaoTrade['modified']) >= strtotime($tradeCloud['jdp_modified'])) {
                $this->runSuccess($tid, "处理交易号[{$tid}]--> 完成：最后更新时间一致");
                continue;
            }*/

            // 判断是否需要更新
            if (!empty($taobaoTrade) && !(empty($taobaoTrade['order_id']) && in_array($taobaoTrade['is_tran_success'], array(0, 1)))) {
                $this->runSuccess($tid, "处理交易号[{$tid}]--> 已转入或者转入中");
                continue;
            }
            $tradeDetail = $tradeCloud['jdp_response']['trade_fullinfo_get_response']['trade'];
            if (empty($tradeDetail['tid'])) {
                $this->runSuccess($tid, "处理交易号[{$tid}]--> 完成：rds 信息不完整");
                continue;
            }
            if (isset($tradeDetail['orders']['order']) && is_array($tradeDetail['orders']['order'])) {
                $tradeDetail['items'] = $tradeDetail['orders']['order'];
                foreach ($this->getTradeItemFields() as $_fields_key) {
                    foreach ((array)$tradeDetail['items'] as $__key=>$__value) {
                        if (!isset($tradeDetail['items'][$__key][$_fields_key])) {
                            $tradeDetail['items'][$__key][$_fields_key] = '';
                        }
                        if (isset($__value['is_sh_ship']) && $__value['is_sh_ship'] == 1) {
                            $tradeDetail['is_sh_ship'] = true;
                        }
                    }
                }
            } else {
                $tradeDetail['items'] = array();
            }
            if (isset($tradeDetail['promotion_details']['promotion_detail']) && is_array($tradeDetail['promotion_details']['promotion_detail'])) {
                $tradeDetail['promotions'] = $tradeDetail['promotion_details']['promotion_detail'];
            } else {
                $tradeDetail['promotions'] = array();
            }
            foreach ($this->getTradeFields() as $_field) {
                if (!isset($tradeDetail[$_field]) || is_null($tradeDetail[$_field])) {
                    $tradeDetail[$_field] = '';
                }
            }
            $shangdian = $shangdianMap[$tradeDetail['seller_nick']] ?? [];
            $this->log_cost('rds trade preprocess');
            if (empty($shangdian)) { // 不是本系统的订单
                $this->runFail($tid, 3, '不是本系统的订单');
                continue;
            }
            $tradeDetail['lylx'] = $shangdian['lylx'];
            $tradeDetail['qd_id'] = $shangdian['qd_id'];
            $tradeDetail['shop_id'] = $shangdian['id'];
            $tradeDetail['trade_type'] = 0;
            // 转入本地
            try {
                \DB::beginTransaction();
                $tradeDetail = $this->preprocess($tradeDetail);
                $this->log_cost('trade preprocess');
                if (isset($taobaoTrade) && !empty($taobaoTrade) && empty($taobaoTrade['order_id']) && in_array($taobaoTrade['is_tran_success'], array(0, 1))) {
                    $updateTrade = $this->parseTaobaoTrade($tradeDetail);
                    $result = \DB::table('taobao_trade')->where('tid', $tid)->update($updateTrade);
                    $this->log_cost('trade update');
                    unset($updateTrade);
                    if (false === $result) {
                        throw new \Exception('更新taobao_trade表失败');
                    }
                    foreach ($tradeDetail['items'] as $item) {
                        $item = $this->parseTaobaoItem($item);
                        $result = \DB::table('taobao_items')->where('tid', $tid)->where('oid', $item['oid'])->update($item);
                        if (false === $result) {
                            throw new Exception('更新taobao_items表失败');
                        }
                    }
                    $this->log_cost('item update');
                    if ($tradeDetail['promotions']) {
                        \DB::delete("DELETE FROM taobao_promotion WHERE tid = '{$tid}'");
                        $promotions = [];
                        foreach ($tradeDetail['promotions'] as $key=>$_promotion) {
                            $_promotion['ttid'] = $taobaoTrade['ttid'];
                            $_promotion['tid'] = $tradeDetail['tid'];
                            $promotions[$key] = $this->parseTaobaoPromotion($_promotion);;
                        }
                        $ret = \DB::table('taobao_promotion')->insert($promotions);
                        $this->log_cost('promotion update');
                        if (!$ret) {
                            throw new Exception('插入taobao_promotion表失败');
                        }
                    }
                } else {
                    $insertTrade = $this->parseTaobaoTrade($tradeDetail);
                    $ttid = \DB::table('taobao_trade')->insertGetId($insertTrade);
                    $this->log_cost('trade insert');
                    unset($insertTrade);
                    if (!$ttid) {
                        throw new Exception('插入taobao_trade表失败');
                    }
                    $insertItems = [];
                    foreach ($tradeDetail['items'] as $item) {
                        $item = $this->parseTaobaoItem($item);

                        $item['ttid'] = $ttid;
                        $item['tid'] = $tradeDetail['tid'];
                        $insertItems[] = $item;
                    }
                    if (!empty($insertItems)) {
                        $result = \DB::table('taobao_items')->insert($insertItems);
                        unset($insertItems);
                        if (!$result) {
                            throw new Exception('插入taobao_items表失败');
                        }
                    }
                    $this->log_cost('items insert');
                    if ($tradeDetail['promotions']) {
                        $promotions = [];
                        foreach ($tradeDetail['promotions'] as $key=>$_promotion) {
                            $_promotion['ttid'] = $ttid;
                            $_promotion['tid'] = $tradeDetail['tid'];
                            $promotions[$key] = $this->parseTaobaoPromotion($_promotion);
                        }
                        $ret = \DB::table('taobao_promotion')->insert($promotions);
                        $this->log_cost('promotion insert');
                        if (!$ret) {
                            throw new Exception('插入taobao_promotion表失败');
                        }
                    }
                    // 调用任务链
                    // Queue::push(new TaobaoTradeTransferJob($tid));
                }
                // 更新状态
                $this->runSuccess($tid, 'success');
                \DB::commit();
            } catch (Exception $e) {
                \DB::rollBack();
                $this->runFail($tid, 2, $e->getMessage());
                continue;
            }
        }
        $successTidArr = array_diff($tid_arr, $this->failTidArr);
        if (!empty($successTidArr)) {
            $successTids = implode("','", $successTidArr);
            \DB::update("update get_taobao_trade set sync_status=1 WHERE tid IN ('{$successTids}')");
        }
        if (!empty($this->failTidArr)) {
            $failTids = implode("','", $this->failTidArr);
            \DB::update("update get_taobao_trade set sync_status=2 WHERE tid IN ('{$failTids}')");
        }
        \Log::info('total:['.count($tid_arr) . ']success: [' . count($successTidArr) . ']fail: ['. count($this->failTidArr) .']', [(microtime(true) - $this->startAt) * 1000, Date('Y-m-d H:i:s'), Date('Y-m-d H:i:s', $this->startAt)]);
        unset($this->failTidArr);
        unset($existTradeMap, $tradeCloudMap, $tradeDetail);

        return true;
    }

    public function runSuccess($tid, $msg)
    {
        $this->result = true;
        $current = microtime(true);
        // \Log::info(__METHOD__, ['--> ' . ($current - $this->startAt) * 1000 . ' <--', $this->logs, $tid, $msg]);
        $this->logAt = $current;
        // \DB::update("update get_taobao_trade set sync_status=1 WHERE tid= '{$tid}'");
    }

    public function runFail($tid, $status, $msg)
    {
        $this->result = false;
        $current = microtime(true);
        // \Log::info(__METHOD__, ['   --> ' . ($current - $this->startAt) * 1000 . ' <--', $this->logs, $tid, $msg]);
        $this->logAt = $current;
        $this->failTidArr[] = $tid;
        // \DB::update("update get_taobao_trade set sync_status={$status} WHERE tid= '{$tid}'");
    }

    private function tradeShangdian($tradeDetail)
    {
        $sellerNick = $tradeDetail['seller_nick'];
        $sellerNicks = \Cache::remember('shangdian_list_cache', 60 * 5, function () {
            $shangdians =Shangdian::where('status', 1)->get();
            $sellerNicks = array();
            foreach ($shangdians as $shangdian) {
                $shangdian = $shangdian->toArray();
                if (!isset($shangdian['api_params'])) {
                    continue;
                }
                $apiParams = $shangdian['api_params'];

                $shangdian['api_params'] = $apiParams;
                $appNick = $apiParams['app_nick'];
                if ($appNick) {
                    $sellerNicks[$appNick] = $shangdian;
                }
            }

            return $sellerNicks;
        });

        return $sellerNicks[$sellerNick] ?? [];
    }

    private function getShangdianAppNickMap()
    {
        $seller_nicks = array(
            'anta安凯专卖店',
            'anta安踏业阳专卖店',
            'anta安踏吉元盛宝专卖店',
            'anta安踏吉元科创专卖店',
            'anta安踏天雅专卖店',
            'anta安踏安发专卖店',
            'anta安踏安大专卖店',
            'anta安踏安宏专卖店',
            'anta安踏安建立专卖店',
            'anta安踏安永专卖店',
            'anta安踏安驰专卖店',
            'anta安踏安驰专卖店特价区',
            'anta安踏安鸿专卖店',
            'anta安踏安鸿博专卖店',
            'anta安踏宏然专卖店',
            'anta安踏尚雄专卖店',
            'anta安踏康誉专卖店',
            'anta安踏建力专卖店',
            'anta安踏思捷悦合专卖店',
            'anta安踏斌辉专卖店',
            'anta安踏新凝动专卖店',
            'anta安踏翔润专卖店',
            'anta安踏超越者专卖店',
            'anta安踏跨域专卖店',
            'anta安踏跨域专卖店特价区',
            'anta安踏鑫动专卖店',
            '安踏勇跃专卖店',
            '安踏安大专卖店',
            '安踏安建阳专卖店',
            '安踏旭标专卖店',
            '安踏炫踏专卖店',
            '安踏爱运动专卖店',
            '安踏超越者店',
        );
        $shangdian_arr = array();
        foreach ($seller_nicks as $key => $seller_nick) {
            $shangdian_arr[$seller_nick] = array(
                'sd_id' => $key,
                'id' => $key,
                'lylx'  => 1,
                'qd_id' => 1,
            );
        }

        return $shangdian_arr;
        $appNicks = \Cache::remember('get_shangdian_appnick_map', 60 * 5, function () {
            $shangdians =Shangdian::where('status', 1)->get();
            $appNicks = array();
            foreach ($shangdians as $shangdian) {
                $shangdian = $shangdian->toArray();
                if (!isset($shangdian['api_params'])) {
                    continue;
                }
                $apiParams = $shangdian['api_params'];

                $shangdian['api_params'] = $apiParams;
                $appNick = $apiParams['app_nick'];
                if ($appNick) {
                    $appNicks[$appNick] = $shangdian;
                }
            }

            return $appNicks;
        });

        return $appNicks;
    }

    public function preprocess($trade) {
        $trade['num'] = 0;
        foreach ($trade['items'] as $_key=>$_items) {
            if (!is_array($_items) || empty($_items)) {
                continue;
            }
            $_items['shop_id'] = $trade['shop_id']; //商店ID
            $_items['tid']     = $trade['tid'];
            $trade['num']     += intval($_items['num']);
            $trade['items'][$_key]     = $_items;
        }
        unset($trade['orders']);

        if ($trade['type'] == 'step') {
            $trade['zhengji_status'] = 2;
            foreach ((array)$trade['items'] as $_item) {
                if (isset($_item['zhengji_status']) && !empty($_item['zhengji_status'])) {
                    $trade['zhengji_status'] = $_item['zhengji_status'];
                    break;
                }
            }
        }

        if ('step' == $trade['type']){
            // sleep(rand(2, 3));
        }
        //淘宝用户处理
        $region_arr['province'] = $trade['receiver_state'];
        $region_arr['city']     = empty($trade['receiver_city']) ? $trade['receiver_district'] : $trade['receiver_city'];
        $region_arr['district'] = $trade['receiver_district'];
        $region = ['province' => 540000, 'city' => 542500, 'district' => 542523];
        // 地址处理
        $trade = array_merge($trade, $region);
        //CRM 会员添加
        $users = array (
            'user_name'			=> addslashes($trade['buyer_nick']),
            'email'				=> addslashes($trade['buyer_email']),
            'mobile_phone'		=> addslashes($trade['receiver_mobile']),
            'sd_id'				=> $trade['shop_id'],
            'reg_time'			=> strtotime($trade['created']),
            //地址信息
            'province'			=> addslashes($trade['province']),
            'city'				=> addslashes($trade['city']),
            'district'			=> addslashes($trade['district']),
            'address'			=> addslashes($trade['receiver_address']),
            'receiver_name'		=> addslashes($trade['receiver_name']),
            'receiver_mobile'	=> $trade['receiver_mobile'],
            'receiver_tel'		=> $trade['receiver_phone'],
            'receiver_zip'		=> $trade['receiver_zip'],
            'lylx'				=> $trade['lylx'],
            'buyer_alipay_no'	=> isset($trade['buyer_alipay_no']) ? $trade['buyer_alipay_no'] : '',
        );

        $add_result = ['user_id' => 0, 'is_black' => 0];;

        $trade['user_id']  = $add_result['user_id'];
        $trade['is_black'] = $add_result['is_black'];
        $trade['receiver_city'] = empty($trade['receiver_city']) ? $trade['receiver_district'] : $trade['receiver_city'];

        if(isset($trade['service_orders']['service_order']) && !empty($trade['service_orders']['service_order'])){
            $service_order = $trade['service_orders']['service_order'];
            foreach($service_order as $_service_order){
                if(isset($_service_order['tmser_spu_code']) && $_service_order['tmser_spu_code'] == '家装干支装服务'){
                    //家装干支装服务费放入订单运费
                    $trade['post_fee'] += $_service_order['payment'];
                }
            }
        }

        //买家已付款等待卖家发货,处理快递公司信息
        if ('WAIT_SELLER_SEND_GOODS' == $trade['status']) {
            $region_arr['province'] = $trade['province'];
            $region_arr['city'] = $trade['city'];
            $region_arr['district'] = $trade['district'];
            $region_arr['shipping_type'] = $trade['shipping_type'];
            $region_arr['shop_id'] = $trade['shop_id'];
            $region_arr['is_cod'] = ('cod' == $trade['type'] ? 1 : 0);
            $region_arr['user_id'] = $trade['user_id'];
            $region_arr['address'] = $trade['receiver_state'] . ' ' . $trade['receiver_city'] . ' ' . $trade['receiver_district'] . ' ' . $trade['receiver_address'];
            $region_arr['transaction_type'] = $trade['type'];
            foreach ($trade['items'] as $items){
                $region_arr['goods_sn'][] = $items['outer_iid'];
            }
            $result = $this->_x_auto_identify_shipping($region_arr,$trade);
            $trade['shipping_id'] = isset($result['data']['shipping']['shipping_id']) ? $result['data']['shipping']['shipping_id'] : 0;
            $trade['shipping_code'] = isset($result['data']['shipping']['shipping_code']) ? $result['data']['shipping']['shipping_code'] : "";
            $trade['shipping_name'] = isset($result['data']['shipping']['shipping_name']) ? $result['data']['shipping']['shipping_name'] : "";
        }

        //菜鸟联盟信息
        if(isset($trade['trade_attr']) && !empty($trade['trade_attr'])){
            $cnlm_info_arr = json_decode($trade['trade_attr'],true);
            $trade['cn_service'] = isset($cnlm_info_arr['cnService']) && $cnlm_info_arr['cnService']?$cnlm_info_arr['cnService']:'';
            $trade['push_time'] = isset($cnlm_info_arr['pushTime']) && $cnlm_info_arr['pushTime']?$cnlm_info_arr['pushTime']:'';
            $trade['buyer_tax_no'] = isset($cnlm_info_arr['buyerTaxNO']) && $cnlm_info_arr['buyerTaxNO']?$cnlm_info_arr['buyerTaxNO']:'';//电子发票税号,企业参数,在RDS勾选后会生效
            $trade['es_date'] = isset($trade['esDate']) && $trade['esDate']?$trade['esDate']:'';
            $trade['es_range'] = isset($trade['esRange']) && $trade['esRange']?$trade['esRange']:'';
            $trade['os_date'] = isset($trade['osDate']) && $trade['osDate']?$trade['osDate']:'';
            $trade['os_range'] = isset($trade['osRange']) && $trade['osRange']?$trade['osRange']:'';
            $trade['store_code'] = isset($trade['storeCode']) && $trade['storeCode']?$trade['storeCode']:'';
            $trade['logistics_company'] = isset($trade['logisticsCompany']) && $trade['logisticsCompany']?$trade['logisticsCompany']:'';
        }

        return $trade;
    }

    protected function _x_auto_identify_shipping(&$region_arr,$order_info) {

        $shipping = array('shipping_id' => 0, 'shipping_name' => '', 'shipping_code' => '');
        $result = array('status' => 1, 'data' => array('shipping' => $shipping), 'message' => '');

        if (!empty($region_arr['shipping_id']) && !empty($region_arr['shipping_name']) && !empty($region_arr['shipping_code'])) {
            return $result;
        }

        //下载的时候自动识别快递公司设置
        $ret = array('status'=>2, 'message'=>'安踏OMS只在仓库适配节点匹配快递策略');
        if ($ret['status'] < 0) {
            $result['message'] = $ret['message'];
        } elseif (!isset($ret['data']) || !isset($ret['data']['shipping'])) {
            $result['message'] = $ret['message'];
        } else {
            $result = $ret;
        }

        return $result;
    }

    public function parseTaobaoTrade($trade)
    {
        $tradeParse = [];
        $fields = [
            'shop_id',
            'end_time',
            'buyer_message',
            'shipping_type',
            'buyer_cod_fee',
            'seller_cod_fee',
            'express_agency_fee',
            'alipay_warn_msg',
            'status',
            'buyer_memo',
            'seller_memo',
            'modified',
            'buyer_flag',
            'seller_flag',
            'trade_from',
            'seller_nick',
            'buyer_nick',
            'title',
            'type',
            'created',
            'iid',
            'price',
            'pic_path',
            'num',
            'tid',
            'alipay_no',
            'payment',
            'discount_fee',
            'adjust_fee',
            'snapshot_url',
            'snapshot',
            'seller_rate',
            'buyer_rate',
            'trade_memo',
            'pay_time',
            'buyer_obtain_point_fee',
            'point_fee',
            'real_point_fee',
            'total_fee',
            'post_fee',
            'buyer_alipay_no',
            'receiver_name',
            'receiver_state',
            'receiver_city',
            'receiver_district',
            'receiver_address',
            'receiver_zip',
            'receiver_mobile',
            'receiver_phone',
            'consign_time',
            'buyer_email',
            'commission_fee',
            'seller_alipay_no',
            'seller_mobile',
            'seller_phone',
            'seller_name',
            'seller_email',
            'available_confirm_fee',
            'has_post_fee',
            'received_payment',
            'cod_fee',
            'cod_status',
            'timeout_action_time',
            'is_3d',
            'num_iid',
            'promotion',
            'invoice_name',
            'buyer_tax_no',
            'alipay_url',
            'is_brand_sale',
            'is_lgtype',
            'is_force_wlb',
            'is_refund_trade',
            'user_id',
            'is_black',
            'shipping_id',
            'shipping_code',
            'shipping_name',
            'trade_type',
            'tran_lock',
            'tran_time',
            'step_trade_status',
            'step_paid_fee',
            'mark_desc',
            'o2o',
            'o2o_guide_id',
            'o2o_shop_id',
            'o2o_guide_name',
            'o2o_shop_name',
            'o2o_delivery',
            'o2o_out_trade_id',
            'invoice_kind',
            'is_allow_step',
            'lastchanged',
            'zhengji_status',
            'omnichannel_param',
            'omni_attr',
            'omni_param',
            'et_shop_id',
            'est_con_time',
            'o2opassport',
            'cn_service',
            'push_time',
            'es_date',
            'es_range',
            'os_date',
            'os_range',
            'store_code',
            'logistics_company',
            'is_sh_ship',
            'shipper',
            'service_order_fee',
        ];
        foreach ($fields as $field) {
            if (isset($trade[$field])) {
                $tradeParse[$field] = isset($trade[$field]) ? $trade[$field] : null;
                // $tradeParse[$field] = $trade[$field];
            }
        }

        return $tradeParse;
    }

    public function parseTaobaoItem($item)
    {
        $parseItem = [];
        $fields = [
            'ttid',
            'tid',
            'shop_id',
            'total_fee',
            'discount_fee',
            'adjust_fee',
            'payment',
            'modified',
            'item_meal_id',
            'status',
            'iid',
            'sku_id',
            'sku_properties_name',
            'item_meal_name',
            'num',
            'title',
            'price',
            'pic_path',
            'seller_nick',
            'buyer_nick',
            'refund_status',
            'oid',
            'outer_iid',
            'outer_sku_id',
            'snapshot_url',
            'snapshot',
            'timeout_action_time',
            'buyer_rate',
            'seller_rate',
            'refund_id',
            'seller_type',
            'num_iid',
            'cid',
            'is_oversold',
            'error_code',
            'error_msg',
            'is_tran_success',
            'part_mjz_discount',
            'lastchanged',
            'estimate_con_time',
            'store_code',
            'customization',
        ];
        foreach ($fields as $field) {
            $parseItem[$field] = isset($item[$field]) ? $item[$field] : null;
        }

        return $parseItem;
    }

    public function parseTaobaoPromotion($promotion)
    {
        $parsePromotion = [];
        $fields = ['ttid', 'tid', 'id', 'promotion_name', 'discount_fee', 'gift_item_name', 'gift_item_id', 'gift_item_num', 'promotion_desc', 'promotion_id'];
        foreach ($fields as $field) {
            $parsePromotion[$field] = isset($promotion[$field]) ? $promotion[$field] : '0';
        }
        $parsePromotion['gift_item_id'] = empty($parsePromotion['gift_item_id']) ? $parsePromotion['gift_item_id'] : 0;
        $parsePromotion['gift_item_num'] = empty($parsePromotion['gift_item_num']) ? $parsePromotion['gift_item_num'] : 0;

        return $parsePromotion;
    }

    private function log_cost($name)
    {
        $current = microtime(true);
        $this->logs[$name] = ($current - $this->logAt) * 1000;
        $this->logAt = $current;
    }
}
