<?php


namespace App\Services\Hub\Adidas;


use App\Models\HubClientLog;
use App\Services\Platform\HttpClient\GuzzleAdapter;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;

/**
 * adidas 客户端
 * $request = (new \App\Services\Hub\Adidas\Request\TradeCreateRequest())->setContent($stdTrade);
 * $result = (new \App\Services\Hub\Adidas\AdidasClient)->execute($request)
 *
 * Class AdidasClient
 * @package App\Services\Hub\Adidas
 *
 * @author linqihai
 * @since 2019/12/26 15:16
 */
class AdidasClient
{
    use ResponseTrait;

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function execute($requests)
    {
        // 校验
        $returnFirst = false;
        if (!is_array($requests)) {
            $returnFirst = true;
            $requests = [$requests];
        }

        $responses = $this->performRequests($requests);
        if ($returnFirst) {
            return current($responses);
        }

        return $responses;
    }

    public function getSign($request)
    {
        $data = $request->getData();
        $clientId = $this->config['app_key'];
        $clientSecret = $this->config['app_secret'];
        $sign = md5($clientId . md5($clientId . serialize($data) . $clientSecret) . md5($clientId . $clientSecret));

        return $sign;
    }

    /**
     * @param array $requests
     *
     * @return array
     */
    protected function performRequests(array $requests = [])
    {
        $apiPath = $this->config['url'];
        $isSimulation = $this->config['simulation'];
        $timeout = $this->config['timeout'];

        $headers = [
            'Marketplace-Type' => $this->config['app_env'],
            'app_id'           => $this->config['app_id'],
            'Content-Type'     => 'application/xml',
            'Source'           => 'adaptor',
            'Simulation'       => $isSimulation,
            'Authorization'    => $this->config['app_auth'],
        ];
        $psr7Requests = $logs = [];
        foreach ($requests as $key => $request) {
            /**
             * @var BaseRequest $request
             */
            if (empty($request->apiPath)) { // 自定义请求地址
                $request->apiPath = $apiPath;
            }
            // $headers['Authorization'] = $this->getSign($request);
            $request->setHeaders($headers);
            $psr7Request = $request->getRequest();
            $psr7Requests[$key] = $psr7Request;
            $logs[$key] = [
                'api_method' => $request->getApiName(),
                'app_name'   => config('app.name', 'adaptor'),
                'keyword'    => $request->getKeyword(), // 关键词
                'class_name' => class_basename($request), // 请求的类名
                'url'        => $request->apiPath,
                'input'      => [
                    'body'    => $request->getData(),
                    'headers' => $headers,
                    'timeout' => $timeout,
                    'format'  => $request->format,
                ],
                'start_at'   => Carbon::now()->toDateTimeString(),
            ];
        }
        $responses = $this->send($psr7Requests, $timeout);
        foreach ($responses as $key => $response) {
            if ($response instanceof RequestException) {
                $message = $response->getMessage();
                $logs[$key]['end_at'] = Carbon::now()->toDateTimeString();
                $logs[$key]['response'] = null;
                $logs[$key]['status_code'] = null;
                $logs[$key]['message'] = $message;
                $logs[$key]['input'] = json_encode($logs[$key]['input']);
                $status = false;
                $result = ['status' => $status, 'data' => '', 'message' => $message];
            } else {
                /**
                 * @var $request BaseRequest $request
                 */
                $request = $requests[$key];
                $logs[$key]['end_at'] = Carbon::now()->toDateTimeString();
                $logs[$key]['response'] = $response->getBody();
                $logs[$key]['status_code'] = $response->getStatusCode();
                // 解析响应格式
                $parsed = $this->parseResponse((string)$response->getBody(), $request->format);
                $message = $parsed['message'];
                $status = $parsed['status'];
                $logs[$key]['message'] = $message;
                $logs[$key]['input']['parsed'] = $parsed['data'];
                $logs[$key]['input'] = json_encode($logs[$key]['input']);
                // 请求响应 回调
                if (method_exists($request, 'responseCallback')) {
                    $request->responseCallback($parsed);
                }

                $result = ['status' => $status, 'data' => $parsed['data'], 'errorCode' => $parsed['errorCode'] ?? 0, 'message' => $message];
            }

            $responses[$key] = $result;
        }
        $this->apiLog($logs);

        return $responses;
    }

    public function send($requests, $timeout = 10)
    {
        $adaptor = app()->make(GuzzleAdapter::class);

        return $adaptor->send($requests, $timeout);
    }

    protected function apiLog($logs)
    {
        try {
            HubClientLog::insert($logs);
        } catch (\Exception $e) {
            \Log::error('insert hub client long fail:' . $e->getMessage());
        }
    }

    protected function soap($info)
    {
        $info = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $info);
        $xml = simplexml_load_string($info);
        $obj = (array)$xml->Body->Fault;
        if (!empty($obj)) {
            return $obj;
        }

        return $xml;
    }
}
