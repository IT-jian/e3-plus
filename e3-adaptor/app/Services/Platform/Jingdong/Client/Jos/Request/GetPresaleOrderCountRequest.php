<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 查询预售订单总数量
 * Class GetPresaleOrderCountRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setUserPin($value)	用户pin
 * @method $this setOrderId($value)	订单ID
 * @method $this setOrderStatusItem($value)	订单状态
 * @method $this setStartTime($value)	查询开始时间
 * @method $this setEndTime($value)	查询结束时间
 * @method $this setSkuID($value)	skuID
 * @method $this setOpenIdBuyer($value)	用户pin
 *
 * jingdong_presale_order_updateOrder_getPresaleOrderCount_response.returnType.data
 */
class GetPresaleOrderCountRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=55&apiId=2868&apiName=jingdong.presale.order.updateOrder.getPresaleOrderCount
     */
    protected $apiName = 'jingdong.presale.order.updateOrder.getPresaleOrderCount';

    protected $paramKeys = [
        'userPin',
        'orderId',
        'orderStatusItem',
        'startTime',
        'endTime',
        'skuID',
        'open_id_buyer',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}