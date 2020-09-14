<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 卖家拒绝退货，目前仅支持天猫退货
 *
 * Class RpReturnGoodsAgreeRequest
 *
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setRefundId($value)
 * @method $this setRefundPhase($value)
 * @method $this setRefundVersion($value)
 * @method $this setRefuseProof($value)
 * @method $this setRefuseReasonId($value)
 *
 * @author linqihai
 * @since 2020/1/2 10:00
 */
class RpReturnGoodsRefuseRequest extends TopRequest
{
    protected $apiName = 'taobao.rp.returngoods.refuse';

    // 内容
    public $contentType = 'multipart';

    protected $paramKeys    = [
        'refundId',
        'refundPhase',
        'refundVersion',
        'refuseProof',
        'refuseReasonId',
    ];

    public function check()
    {
    }
}