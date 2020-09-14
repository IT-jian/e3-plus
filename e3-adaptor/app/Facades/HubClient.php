<?php


namespace App\Facades;

use App\Services\Hub\Contracts\HubClientContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static HubClientContract hub(string $name = null)
 * @method \App\Services\Hub\Adidas\Request\RequestContract resolveRequestClass($method) 解析类
 * @method array execute($requests) 执行
 * 订单
 * @method array tradeCreate($trade) 退单创建
 * @method array tradeAddressModify($trade) 订单地址变更
 * @method array tradeAdvanceStatusModify($trade) 预售订单状态更新
 * 退货退款
 * @method array tradeCancel($refund) 订单发货之前申请退款，并且退款成功之后下发
 * @method array refundReturnCreate($refund) 退单创建, 订单发货之后，申请退货退款，并且卖家已同意退货请求
 * @method array refundReturnCancel($refund) 退单取消, 订单发货之后，申请退货退款，消费者关闭或者超时自动关闭的退款请求
 * @method array refundReturnLogisticModify($refund) 退单下发之后，获取到消费者填写了退货物流信息之后，需要下发
 * 退款 gwc
 * @method array refundCreate($refund) 订单已经发货，消费者申请退运费等部分只退款不退货的退款请求，发货之后仅退款
 * @method array refundCancel($refund) 订单已经发货，仅退款，取消退款申请
 * 换货单
 * @method array exchangeCreate($exchange) 换货单创建 换货订单下载，并且状态为卖家已同意的进行下发
 * @method array exchangeCancel($exchange) 换货申请消费者主动取消或者超时关闭的，需要下发
 * @method array exchangeReturnLogisticModify($exchange) 退单下发之后，获取到消费者填写了退货物流信息之后
 *
 * @method array invoiceQuery($tid) 发票内容请求
 *
 * @method array getBody() 获取请求报文
 * @method array get() 获取请求报文
 *
 * @see \App\Services\Hub\HubClientManager
 */
class HubClient  extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hubclient';
    }
}
