<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * taobao.refund.get( 获取单笔退款详情 )
 *
 * Class RefundGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value)
 * @method $this setRefundId($value)
 *
 * @author linqihai
 * @since 2020/7/18 21:07
 * @see https://open.taobao.com/api.htm?docId=46&docType=2
 */
class RefundGetRequest extends TopRequest
{
    protected $apiName = 'taobao.refund.get';

    protected $commaSeparatedParams = [
        'fields',
    ];
    protected $defaultParamValues = [
        'fields' => 'refund_id,alipay_no,tid,oid,buyer_nick,seller_nick,total_fee,status,created,refund_fee,good_status,has_good_return,payment,reason,desc,num_iid,title,price,num,good_return_time,company_name,sid,address,shipping_type,refund_remind_timeout,refund_phase,refund_version,operation_contraint,attribute,outer_id,sku',
    ];

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'fields',
        'refundId',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
