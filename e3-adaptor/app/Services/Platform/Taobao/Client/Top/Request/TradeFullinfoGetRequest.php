<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 获取单笔交易的详细信息
 *
 * Class TradesSoldGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value)
 * @method $this setTid($value)
 *
 * @author linqihai
 * @since 2020/7/18 21:07
 * @see https://open.taobao.com/api.htm?docId=46&docType=2
 */
class TradeFullinfoGetRequest extends TopRequest
{
    protected $apiName = 'taobao.trade.fullinfo.get';

    protected $commaSeparatedParams = [
        'fields',
    ];
    protected $defaultParamValues = [
        'fields' => 'tid,tid_str,status,type,seller_nick,buyer_nick,created,modified,adjust_fee,alipay_no,alipay_point,available_confirm_fee,buyer_alipay_no,buyer_area,buyer_cod_fee,buyer_email,buyer_obtain_point_fee,buyer_rate,cod_fee,cod_status,coupon_fee,commission_fee,discount_fee,end_time,est_con_time,has_post_fee,is_3D,is_brand_sale,is_daixiao,is_force_wlb,is_sh_ship,is_lgtype,is_part_consign,is_wt,is_gift,num,num_iid,new_presell,nr_shop_guide_id,nr_shop_guide_name,o2o_step_trade_detail,o2o_step_trade_detail_new,orders,pay_time,payment,pcc_af,pic_path,platform_subsidy_fee,point_fee,post_fee,price,promotion_details,real_point_fee,received_payment,receiver_address,receiver_city,receiver_country,receiver_district,receiver_mobile,receiver_name,receiver_state,receiver_town,receiver_zip,seller_alipay_no,seller_can_rate,seller_cod_fee,seller_email,seller_flag,seller_mobile,seller_name,seller_phone,seller_rate,send_time,service_tags,service_type,shipping_type,sid,snapshot_url,step_paid_fee,step_trade_status,title,total_fee,trade_from',
    ];

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'fields',
        'tid',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
