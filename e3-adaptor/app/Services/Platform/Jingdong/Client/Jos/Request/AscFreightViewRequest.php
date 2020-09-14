<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 查看服务单的运单信息
 *
 * Class AscFreightViewRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setBuId($value) 商家编号 
 * @method $this setOperatePin($value) 操作人账号 
 * @method $this setOperateNick($value) 操作人姓名 
 * @method $this setServiceId($value) 服务单号 
 * @method $this setOrderId($value) 订单号 
 * @method $this setExtJsonStr($value) 扩展条件（JSON格式） 

 *
 * jingdong_asc_freight_view_responce.result.data
 */
class AscFreightViewRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://jos.jd.com/api/detail.htm?apiName=jingdong.asc.receive.list&id=2114
     */
    protected $apiName = 'jingdong.asc.freight.view';

    protected $paramKeys = [
        'buId',
        'operatePin',
        'operateNick',
        'serviceId',
        'orderId',
        'extJsonStr',
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
    }
}