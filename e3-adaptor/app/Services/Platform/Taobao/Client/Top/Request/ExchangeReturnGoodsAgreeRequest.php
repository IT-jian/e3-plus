<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 换货单卖家同意确认收货
 *
 * Class ExchangeReturnGoodsAgreeRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setDisputeId($value)
 * @method $this setFields($value)
 *
 * @author linqihai
 * @since 2020/1/2 10:29
 */
class ExchangeReturnGoodsAgreeRequest extends TopRequest
{
    protected $apiName = 'tmall.exchange.returngoods.agree';

    /**
     * 逗号分隔的字段
     *
     * @var array
     */
    protected $commaSeparatedParams = [
        'fields',
    ];

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys    = [
        'fields',
        'disputeId',
    ];

    /**
     * 默认值字段
     *
     * @var array
     */
    protected $defaultParamValues = [
        'fields' => 'dispute_id,bizorder_id,modified,status',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}