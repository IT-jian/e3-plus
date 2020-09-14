<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 拆包登记--同意收货
 *
 * Class AscReceiveRegisterRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setBuId($value) 商家ID（最长50） 
 * @method $this setOperatePin($value) 操作人账号（最长50） 
 * @method $this setOperateNick($value) 操作人姓名（最长50） 
 * @method $this setServiceId($value) 服务单号 
 * @method $this setOrderId($value) 订单号 
 * @method $this setReceivePin($value) 收货人账号 
 * @method $this setReceiveName($value) 收货人姓名 
 * @method $this setPackingState($value) 产品包装状况 
 * @method $this setQualityState($value) 主商品功能状况 
 * @method $this setInvoiceRecord($value) 发票登记状况 
 * @method $this setJudgmentReason($value) 收货登记原因 
 * @method $this setAccessoryOrGift($value) 附件/赠品 
 * @method $this setAppearanceState($value) 主商品外观 
 * @method $this setReceiveRemark($value) 收货备注
 *
 * jingdong_asc_receive_register_response.result.success
 */
class AscReceiveRegisterRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://jos.jd.com/api/detail.htm?apiName=jingdong.asc.receive.list&id=2114
     */
    protected $apiName = 'jingdong.asc.receive.register';

    protected $paramKeys = [
        'buId', //商家ID（最长50） 
        'operatePin', //操作人账号（最长50） 
        'operateNick', //操作人姓名（最长50） 
        'serviceId', //服务单号 
        'orderId', //订单号
        'receivePin', //收货人账号
        'receiveName', //收货人姓名
        'packingState', //产品包装状况
        'qualityState', //主商品功能状况
        'invoiceRecord', //发票登记状况
        'judgmentReason', //收货登记原因
        'accessoryOrGift', //附件赠品
        'appearanceState', //主商品外观
        'receiveRemark', //收货备注
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
