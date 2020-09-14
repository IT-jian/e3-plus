<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 输入单个SOP订单id，得到所有相关订单信息
 *
 * Class PopOrderGetRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 *
 * jingdong_pop_order_get_responce.orderDetailInfo.orderInfo
 */
class PopOrderGetRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=55&apiId=4247&apiName=jingdong.pop.order.get
     */
    protected $apiName = 'jingdong.pop.order.get';

    protected $paramKeys = [
        'order_id',
        'optional_fields',
        'order_state',
    ];

    public function setOrderId($value)
    {
        $this->setData(['order_id' => $value], true);

        $fields = 'orderId,venderId,orderType,payType,orderTotalPrice,orderSellerPrice,orderPayment,freightPrice,sellerDiscount,orderState,orderStateRemark,deliveryType,invoiceEasyInfo,invoiceInfo,invoiceCode,orderRemark,orderStartTime,orderEndTime,consigneeInfo,itemInfoList,couponDetailList,venderRemark,balanceUsed,pin,returnOrder,paymentConfirmTime,waybill,logisticsId,vatInfo,modified,directParentOrderId,parentOrderId,customs,customsModel,orderSource,storeOrder,idSopShipmenttype,scDT,serviceFee,pauseBizInfo,taxFee,tuiHuoWuYou,orderSign,storeId,menDianId,mdbStoreId,salesPin,originalConsigneeInfo';
        $this->setOptionalFields($fields);

        return $this;
    }

    public function setOptionalFields($value)
    {
        $this->setData(['optional_fields' => $value], true);

        return $this;
    }

    public function setOrderState($value)
    {
        $this->setData(['order_state' => $value], true);

        return $this;
    }

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->buId, "buId");
        //RequestCheckUtil::checkNotNull($this->operatePin, "operatePin");
        //RequestCheckUtil::checkNotNull($this->operateNick, "operateNick");
    }
}
