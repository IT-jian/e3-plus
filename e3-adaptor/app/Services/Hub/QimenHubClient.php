<?php


namespace App\Services\Hub;


use App\Services\Hub\Adidas\ResponseTrait;
use App\Services\Hub\Contracts\HubClientContract;
use App\Services\Platform\Taobao\Qimen\Top\QimenCloudClient;

/**
 * Class AdidasHubClient
 * @package App\Services\Hub
 * 订单
 * @method array tradeCreate($trade) √订单创建
 * 退货退款
 * @method array refundReturnCreate($refund) √退单创建, 订单发货之后，申请退货退款，并且卖家已同意退货请求
 * 换货单
 * @method array exchangeCreate($exchange) √换货单创建 换货订单下载，并且状态为卖家已同意的进行下发
 *
 */
class QimenHubClient implements HubClientContract
{
    use ResponseTrait;

    private static $methodClassMap = [];

    private $platform = '';

    /**
     * @var QimenCloudClient
     */
    private $client;

    public function __construct(QimenCloudClient $client)
    {
        $this->client = $client;
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
        $requestType = 'Adidas\\';
        $className = '\App\Services\Hub\Qimen\\' . $requestType . 'Request\\';
        $className .= ucfirst($method).'Request';
        if (!class_exists($className)) {
            throw new \BadMethodCallException('No Such Hub API ' . $method);
        }

        return app()->make($className);
    }

    /**
     * @param array $requests[]
     * @return mixed
     * @throws \Exception
     */
    public function execute($requests)
    {
        $returnFirst = false;
        if (!is_array($requests)) {
            $returnFirst = true;
            $requests = [$requests];
        }

        $qimenRequest = $result = $results = [];
        foreach ($requests as $key => $request) {
            $shopCode = $request->getShop();
            $qimenRequest[$key] = $this->client->shop($shopCode)->getRequest($request->qimenRequest);
        }

        $responses = $this->client->send($qimenRequest);
        foreach ($responses as $key => $resp) {
            $results[$key] = $this->client->parseResponse($resp);
        }
        foreach ($results as $key => $response) {
            if (isset($response['code']) && 1 == $response['code']) {
                // 处理响应结构
                $result[$key] = $this->parseResponse($response['message']);
            } else {
                $result[$key] = ['status' => false, 'data' => $response, 'message' => $response['message']];
            }
        }
        \Log::error('qimen response', $result);
        if ($returnFirst) {
            return current($result);
        }
        return $result;
    }
}
