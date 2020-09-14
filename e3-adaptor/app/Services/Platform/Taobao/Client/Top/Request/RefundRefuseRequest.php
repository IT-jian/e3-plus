<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 卖家拒绝单笔退款（包含退款和退款退货）交易
 * 1. 传入的refund_id和相应的tid, oid必须匹配
 * 2. 如果一笔订单只有一笔子订单，则tid必须与oid相同
 * 3. 只有卖家才能执行拒绝退款操作
 * 4. 以下三种情况不能退款：卖家未发货；7天无理由退换货；网游订单
 *
 * Class RefundRefuseRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setRefundId($value)
 * @method $this setRefuseMessage($value)
 * @method $this setRefuseProof($value)
 * @method $this setRefundPhase($value)
 * @method $this setRefundVersion($value)
 * @method $this setRefuseReasonId($value)
 *
 * @author linqihai
 * @since 2020/1/2 10:13
 */
class RefundRefuseRequest extends TopRequest
{
    protected $apiName = 'taobao.refund.refuse';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys    = [
        'refundId',
        'refuseMessage',
        'refuseProof',
        'refundPhase',
        'refundVersion',
        'refuseReasonId',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}