<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 审核退款单
 *
 * Class RpRefundReviewRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setRefundId($value)
 * @method $this setRefundVersion($value)
 * @method $this setRefundPhase($value)
 * @method $this setOperator($value)
 * @method $this setResult($value)
 * @method $this setMessage($value)
 *
 * @author linqihai
 * @since 2020/7/6 12:07
 * @see https://open.taobao.com/api.htm?docId=23875&docType=2&source=search
 */
class RpRefundReviewRequest extends TopRequest
{
    protected $apiName = 'taobao.rp.refund.review';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys    = [
        'refundId',
        'operator',
        'refundPhase',
        'refundVersion',
        'result',
        'message',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
