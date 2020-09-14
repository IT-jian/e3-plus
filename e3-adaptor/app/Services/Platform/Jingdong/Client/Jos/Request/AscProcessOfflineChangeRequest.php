<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;

/**
 * 线下换新 -- 换货单回写
 *
 * Class AscProcessOfflineChangeRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setBuId($value) 商家编号
 * @method $this setOperatePin($value) 操作人账号
 * @method $this setOperateNick($value) 操作人姓名
 * @method $this setOperateRemark($value) 操作备注
 * @method $this setServiceId($value) 服务单号
 * @method $this setOrderId($value) 订单号
 * @method $this setSysVersion($value) 服务单版本号
 * @method $this setOpFlag($value) 操作类型 1：线下换新，2：线下换新未解决
 * @method $this setPartExpressId($value) 运单Id
 * @method $this setShipWayId($value) 承运商
 * @method $this setShipWayName($value) 承运商名称
 * @method $this setExpressCode($value) 货运单号
 * @method $this setRelationBillId($value) 关联单号
 * @method $this setWareType($value) 商品类型
 * @method $this setPartSrc($value) 来源 发货组：10，发票组：20
 * @method $this setExtJsonStr($value) 扩展条件
 * @method $this setWareNum($value) 商品数量
 * jingdong_asc_process_offline_change_responce.result.success
 */
class AscProcessOfflineChangeRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.taobao.com/api.htm?spm=a219a.7386797.0.0.4afc669aOKdRAr&source=search&docId=10690&docType=2
     */
    protected $apiName = 'jingdong.asc.process.offline.change';

    protected $paramKeys = [
        'buId',
        'operatePin',
        'operateNick',
        'operateRemark',
        'serviceId',
        'orderId',
        'sysVersion',
        'opFlag',
        'partExpressId',
        'shipWayId',
        'shipWayName',
        'expressCode',
        'relationBillId',
        'wareType',
        'partSrc',
        'extJsonStr',
        'wareNum',
    ];

    protected $defaultParamValues = [
        'operatePin'  => 'adaptor',
        'operateNick' => 'adaptor',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->operatePin, "operatePin");
        //RequestCheckUtil::checkNotNull($this->operateNick, "operateNick");
        //RequestCheckUtil::checkNotNull($this->buId, "buId");
        //RequestCheckUtil::checkNotNull($this->serviceId, "serviceId");
        //RequestCheckUtil::checkNotNull($this->orderId, "orderId");
        //RequestCheckUtil::checkNotNull($this->sysVersion, "sysVersion");
        //RequestCheckUtil::checkNotNull($this->opFlag, "opFlag");
        //RequestCheckUtil::checkNotNull($this->shipWayId, "shipWayId");
        //RequestCheckUtil::checkNotNull($this->shipWayName, "shipWayName");
        //RequestCheckUtil::checkNotNull($this->expressCode, "expressCode");
        //RequestCheckUtil::checkNotNull($this->wareType, "wareType");
    }
}