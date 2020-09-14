<?php


namespace App\Services\Platform\Jingdong\Hufu;


use App\Services\Hub\Adidas\AdidasClient;
use App\Services\Hub\Adidas\BaseRequest;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;

class HufuCloudClient extends AdidasClient
{
    protected function performRequests(array $requests = [])
    {
        $apiPath = $this->config['url'];
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
                    'body'    => $request->getData(),
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

                $result = ['status' => $status, 'data' => $parsed['data'], 'errorCode' => $parsed['errorCode'] ?? 0, 'message' => $message];
            }

            $responses[$key] = $result;
        }
        $this->apiLog($logs);

        return $responses;
    }

    public function parseResponse($responseString, $format = "json")
    {
        $decodedResponse = json_decode($responseString, true);
        if (null !== $decodedResponse) {
            if (isset($decodedResponse['flag']) && 'success' == $decodedResponse['flag']) {
                $result = parent::parseResponse($decodedResponse['message']);;
            } else {
                $result = ['status' => false, 'data' => [$responseString], 'message' => 'parse json response fail：' . $responseString];
            }
        } else {
            $result = ['status' => false, 'data' => [$responseString], 'message' => 'parse json response fail：' . $responseString];
        }

        return $result;
    }
}
