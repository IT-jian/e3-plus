<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 淘宝换货单详情
 *
 * Class ExchangeGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value))
 * @method $this setDisputeId($value)
 *
 * @author linqihai
 * @since 2019/12/23 16:40
 */
class ExchangeGetRequest extends TopRequest
{

    protected $apiName = 'tmall.exchange.get';

    protected $commaSeparatedParams = [
        'fields',
    ];
    protected $paramKeys    = [
        'fields',
        'disputeId',
    ];
    protected $defaultParamValues = [
        'fields' => 'dispute_id,bizorder_id,num,buyer_nick,status,created,modified,reason,title,buyer_logistic_no,
        seller_logistic_no,refund_version,refund_phase,good_status,price,bought_sku,exchange_sku,buyer_address,
        address,time_out,buyer_phone,buyer_logistic_name,seller_logistic_name,alipay_no,buyer_name,seller_nick,desc',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
