<?php


namespace App\Services\Hub;


use App\Services\Hub\Adidas\AdidasClient;
use App\Services\Hub\Contracts\HubClientContract;
use Illuminate\Support\Str;

/**
 * Class AdidasHubClient
 * @package App\Services\Hub
 * 订单
 * @method array tradeCreate($trade) √订单创建
 * @method array tradeAddressModify($trade) doing 订单地址变更
 * @method array tradeAdvanceStatusModify($trade) 预售订单状态更新
 * 退货退款
 * @method array tradeCancel($refund) √订单发货之前申请退款，并且退款成功之后下发
 * @method array refundReturnCreate($refund) √退单创建, 订单发货之后，申请退货退款，并且卖家已同意退货请求
 * @method array refundReturnCancel($refund) √退单取消, 订单发货之后，申请退货退款，消费者关闭或者超时自动关闭的退款请求
 * @method array refundReturnLogisticModify($refund) √退单下发之后，获取到消费者填写了退货物流信息之后，需要下发
 * 仅退款 gwc
 * @method array refundCreate($refund) √订单已经发货，消费者申请退运费等部分只退款不退货的退款请求，发货之后仅退款
 * @method array refundCancel($refund) doing 订单已经发货，仅退款，取消退款申请
 * 换货单
 * @method array exchangeCreate($exchange) √换货单创建 换货订单下载，并且状态为卖家已同意的进行下发
 * @method array exchangeCancel($exchange) √换货申请消费者主动取消或者超时关闭的，需要下发
 * @method array exchangeReturnLogisticModify($exchange) doing 换货申请消费者主动取消或者超时关闭的，需要下发
 *
 * @author linqihai
 * @since 2020/1/6 14:53
 */
class AdidasHubClient implements HubClientContract
{
    protected static $methodClassMap = [];

    protected $platform = '';

    /**
     * @var AdidasClient
     */
    protected $client;

    public function __construct(AdidasClient $client, $platform = 'taobao')
    {
        $this->client = $client;
        $this->platform = $platform;
    }

    /**
     * 自动解析 request 实例
     *
     * @param $method
     * @param $parameters
     * @return array
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/6 14:36
     */
    public function __call($method, $parameters)
    {
        $request = $this->resolveRequestClass($method);
        // 设置请求内容
        $request = $request->setContent(...$parameters);

        $result = $this->execute([$request]);

        return current($result);
    }

    public function platform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * @param $method
     * @return \App\Services\Hub\Adidas\Request\RequestContract
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/6 14:29
     */
    public function resolveRequestClass($method)
    {
        $requestType = $this->platform == 'taobao' ? '' : Str::ucfirst($this->platform);
        $className = '\App\Services\Hub\Adidas\\' . $requestType . 'Request\\';
        $className .= ucfirst($method).'Request';
        if (!class_exists($className)) {
            throw new \BadMethodCallException('No Such Hub API ' . $method);
        }

        return app()->make($className);
    }

    public function execute($requests)
    {
        return $this->client->execute($requests);
    }
}
