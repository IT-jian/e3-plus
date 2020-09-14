<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 换货单卖家拒绝确认收货
 *
 * Class ExchangeReturnGoodsRefuseRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setDisputeId($value)
 * @method $this setFields($value)
 * @method $this setSellerRefuseReasonId($value)
 * @method $this setLeaveMessage($value)
 * @method $this setLeaveMessagePics($value)
 *
 * @author linqihai
 * @since 2020/1/2 10:29
 */
class ExchangeReturnGoodsRefuseRequest extends TopRequest
{
    protected $apiName = 'tmall.exchange.returngoods.refuse';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys    = [
        'disputeId',
        'sellerRefuseReasonId',
        'leaveMessage',
        'leaveMessagePics',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}