<?php


namespace App\Services\Hub\Adidas;


use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;

/**
 * aisino 客户端
 *
 * Class AisinoClient
 * @package App\Services\Hub\Adidas
 */
class AisinoClient extends AdidasClient
{
    protected $config;

    protected $headers = [];

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
        $apiPath = $this->config['url'] ?? '';
        $timeout = $this->config['timeout'] ??  5;

        $psr7Requests = $logs = [];
        foreach ($requests as $key => $request) {
            /**
             * @var BaseRequest $request
             */
            /**
             * @var BaseRequest $request
             */
            if (empty($request->apiPath)) { // 自定义请求地址
                $request->apiPath = $apiPath;
            }
            $request->setHeaders($this->headers);

            $psr7Request = $request->getRequest();
            $psr7Requests[$key] = $psr7Request;
            $logs[$key] = [
                'api_method' => $request->getApiName(),
                'app_name'   => config('app.name', 'adaptor'),
                'keyword'    => $request->getKeyword(), // 关键词
                'class_name' => class_basename($request), // 请求的类名
                'url'        => $apiPath,
                'input'      => [
                    'body'    => $request->getData(),
                    'headers' => $this->headers,
                    'timeout' => $timeout,
                    'format'  => $request->format,
                ],
                'start_at'   => Carbon::now()->toDateTimeString(),
            ];
        }
        $responses = $this->send($psr7Requests);
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
                // 请求响应 回调
                if (method_exists($request, 'responseCallback')) {
                    $request->responseCallback($parsed);
                }

                $result = ['status' => $status, 'data' => $parsed['data'], 'message' => $message];
            }

            $responses[$key] = $result;
        }
        $this->apiLog($logs);

        return $responses;
    }

    public function parseResponse(\Psr\Http\Message\ResponseInterface $response, $format = "xml")
    {
        $decodedResponse = json_decode((string)$response->getBody(), true);
        if (null !== $decodedResponse) {
            $result = $decodedResponse;
            $isSuccess = '0000' == data_get($result, 'status', '0001');
            $result = ['status' => $isSuccess, 'message' => $result['message'] ?? '', 'data' => $result];
        } else {
            throw new \RuntimeException('unknown format: ' . $format);
        }

        return $result;
    }
}
