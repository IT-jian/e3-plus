<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;

/**
 * Class LogisticsOfflineSendRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setOrderId($tid) 订单号
 * @method $this setLogiCoprId($shippingCode) 物流供公司ID，只可通过获取商家物流公司接口获得。多个物流公司以|分隔
 * @method $this setLogiNo($shippingSn) 运单号，不同物流公司的运单号用|分隔，如果同一物流公司有多个运单号，则用英文逗号分隔
 * @method $this setInstallId($value)
 * @method string getOrderId()
 * @method string getLogiCoprId()
 * @method string getLogiNo()
 *
 * @author linqihai
 * @since 2019/12/31 16:32
 */
class LogisticsOfflineSendRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.taobao.com/api.htm?spm=a219a.7386797.0.0.4afc669aOKdRAr&source=search&docId=10690&docType=2
     */
    protected $apiName = 'jingdong.pop.order.shipment';

    protected $paramKeys = [
        'orderId',
        'logiCoprId',
        'logiNo',
        'installId',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}