<?php


namespace App\Services\Wms\Shunfeng\Adidas;


use App\Services\Platform\HttpClient\GuzzleAdapter;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;

/**
 * adidas wms 客户端
 *
 * Class ShunfengAdidasClient
 * @package App\Services\Hub\Adidas
 *
 * @author linqihai
 * @since 2019/12/26 15:16
 */
class ShunfengAdidasClient
{
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

        $psr7Requests = $logs = [];
        foreach ($requests as $key => $request) {
            /**
             * @var BaseRequest $request
             */
            if (empty($request->apiPath)) { // 自定义请求地址
                $request->apiPath = $apiPath;
            }
            $psr7Request = $request->getRequest();
            $psr7Requests[$key] = $psr7Request;
            $logs[$key] = [
                'api_method' => $request->getApiName(),
                'app_name'   => config('app.name', 'adaptor'),
                'keyword'    => $request->getKeyword(), // 关键词
                'class_name' => class_basename($request), // 请求的类名
                'url'        => $request->apiPath,
                'input'      => [
                    'body'    => [
                        (string)$psr7Request->getBody(),
                        $request->getData(),
                    ],
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
                $parsed = $this->parseResponse($response, $request->format);
                $message = $parsed['message'];
                $status = $parsed['status'];
                $logs[$key]['message'] = $message;
                $logs[$key]['input']['parsed'] = $parsed['data'];
                $logs[$key]['input'] = json_encode($logs[$key]['input']);

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

    protected function parseResponse(\Psr\Http\Message\ResponseInterface $response, $format = "json")
    {
        $md5 = md5($this->config['system_key'], false);
        $key = substr($md5, 8, 16);
        $responseBody = (string)$response->getBody()->getContents();
        $decrypt = openssl_decrypt(base64_decode($responseBody), 'aes-128-ecb', $key, OPENSSL_RAW_DATA);
        $decodedResponse = json_decode($decrypt, true);
        if (null !== $decodedResponse) {
            $result = $decodedResponse;
        } else {
            return ['status' => false, 'data' => [$responseBody], 'message' => '解析结果异常'];
        }

        return ['status' => true, 'data' => $result, 'message' => ''];
    }

    protected function apiLog($logs)
    {
        try {
            \App\Models\AdidasWmsClientLog::insert($logs);
        } catch (\Exception $e) {
            \Log::error('insert adidas wms client long fail:' . $e->getMessage());
        }
        // \Log::channel('wms_client')->debug('', $logs);
    }
}
