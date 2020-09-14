<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 拒绝退款--取消服务单
 *
 * Class AscCommonCancelRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setBuId($value) 商家ID（最长50）
 * @method $this setOperatePin($value) 操作人账号（最长50）
 * @method $this setOperateNick($value) 操作人姓名（最长50）
 * @method $this setServiceId($value) 服务单号
 * @method $this setOrderId($value) 订单号
 * @method $this setApproveNotes($value) 审核意见
 * @method $this setSysVersion($value) 服务单版本号
 * @method $this setOperateRemark($value) 操作备注
 * @method $this setExtJsonStr($value) 扩展条件（JSON格式）
 *
 * jingdong_asc_common_cancel_response.result.success
 */
class AscCommonCancelRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://jos.jd.com/api/detail.htm?apiName=jingdong.asc.receive.list&id=2114
     */
    protected $apiName = 'jingdong.asc.common.cancel';

    protected $paramKeys = [
        'buId',
        'operatePin',
        'operateNick',
        'serviceId',
        'orderId',
        'approveNotes',
        'sysVersion',
    ];

    protected $defaultParamValues = [
        'operatePin' => 'adaptor',
        'operateNick' => 'adaptor',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->buId, "buId");
        //RequestCheckUtil::checkNotNull($this->operatePin, "operatePin");
        //RequestCheckUtil::checkNotNull($this->operateNick, "operateNick");
        //RequestCheckUtil::checkNotNull($this->serviceId, "serviceId");
        //RequestCheckUtil::checkNotNull($this->orderId, "orderId");
        //RequestCheckUtil::checkNotNull($this->receivePin, "receivePin");
        //RequestCheckUtil::checkNotNull($this->receiveName, "receiveName");
        //RequestCheckUtil::checkNotNull($this->packingState, "packingState");
        //RequestCheckUtil::checkNotNull($this->qualityState, "qualityState");
        //RequestCheckUtil::checkNotNull($this->invoiceRecord, "invoiceRecord");
        //RequestCheckUtil::checkNotNull($this->judgmentReason, "judgmentReason");
        //RequestCheckUtil::checkNotNull($this->accessoryOrGift, "accessoryOrGift");
        //RequestCheckUtil::checkNotNull($this->appearanceState, "appearanceState");
        //RequestCheckUtil::checkNotNull($this->receiveRemark, "receiveRemark");
    }
}