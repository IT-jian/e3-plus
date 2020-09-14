<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 淘宝换货单列表
 *
 * @method $this setFields($value)
 * @method $this setStartGmtModifiedTime($value)
 * @method $this setEndGmtModifedTime($value)
 * @method $this setLogisticNo($value)
 * @method $this setBuyerNick($value)
 * @method $this setStartCreatedTime($value)
 * @method $this setPageSize($value)
 * @method $this setDisputeStatusArray($value)
 * @method $this setEndCreatedTime($value)
 * @method $this setBuyerId($value)
 * @method $this setRefundIdArray($value)
 * @method $this setPageNo($value)
 * @method $this setBizOrderId($value)
 *
 * Class ExchangeReceiveGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @author linqihai
 * @since 2019/12/23 16:40
 */
class ExchangeReceiveGetRequest extends TopRequest
{
    protected $apiName = 'tmall.exchange.receive.get';

    protected $commaSeparatedParams = [
        'fields',
    ];

    protected $paramKeys = [
        'fields',
        'startGmtModifiedTime',
        'endGmtModifedTime',
        'logisticNo',
        'buyerNick',
        'startCreatedTime',
        'pageSize',
        'disputeStatusArray',
        'endCreatedTime',
        'buyerId',
        'refundIdArray',
        'pageNo',
        'bizOrderId',
    ];

    protected $defaultParamValues = [
        'fields' => 'dispute_id, bizorder_id, num, buyer_nick, status, created, modified, reason, title, buyer_logistic_no, 
                    seller_logistic_no, bought_sku, exchange_sku, buyer_address, address, buyer_phone, buyer_logistic_name, 
                    seller_logistic_name, alipay_no, buyer_name, seller_nick',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}