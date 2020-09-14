<?php


namespace App\Services\Hub\Adidas;


trait ResponseTrait
{
    public function parseResponse($responseString, $format = "xml")
    {
        $decodedResponse = json_decode($responseString, true);
        if (null !== $decodedResponse) {
            $format = 'json';
        } else {
            $format = 'xml';
        }
        if ("json" === $format) {
            if (null !== $decodedResponse) {
                $result = $decodedResponse;
                if ($firstResult = data_get($result, 'OrderLines.OrderLine.0', [])) {
                    $status = true;
                    $message = $firstResult['ResponseMessage'] ?? '';
                    $errorCode = $firstResult['ErrorCode'] ?? null;
                    // 仅有 501 错误时，需要重试
                    if (isset($firstResult['ErrorCode']) && in_array($firstResult['ErrorCode'], ['501'])) {
                        $status = false;
                    }
                    $result = ['status' => $status, 'errorCode' => $errorCode, 'data' => $result, 'message' => $message];
                } else if ($cancelErrorCode = data_get($result, 'OrderLines.OrderLine.ResponseCode', '')) {
                    $status = true;
                    $errorResult = data_get($result, 'OrderLines.OrderLine', '');
                    $message = $errorResult['ResponseMessage'] ?? '';
                    $errorCode = $errorResult['ErrorCode'] ?? null;
                    // 仅有 501 错误时，需要重试
                    if (in_array($errorCode, ['501'])) {
                        $status = false;
                    }
                    $result = ['status' => $status, 'errorCode' => $errorCode, 'data' => $result, 'message' => $message];
                } else if (isset($result['ResponseCode'])) { // 标准格式返回成功和失败
                    $status = true;
                    if ('FAIL' == $result['ResponseCode']) {
                        $status = false;
                    }
                    $message = $result['ResponseMessage'] ?? '';

                    $result = ['status' => $status, 'data' => $result, 'message' => $message];
                } else if (isset($result['errors'])) {
                    $error = current($result['errors']);
                    $result = ['status' => false, 'data' => $result, 'errorCode' => $error['ErrorCode'] ?? '', 'message' => $error['ErrorDescription'] ?? ''];
                } else if (isset($result['Errors'])) {
                    $error = $result['Errors']['Error'] ?? $result['Errors'];
                    $result = ['status' => false, 'data' => $result, 'errorCode' => $error['ErrorCode'] ?? '', 'message' => $error['ErrorDescription'] ?? ''];
                } else if (isset($result['Order'])) { // 发票响应
                    $result = ['status' => true, 'data' => $result, 'message' => 'success'];
                }  else if (isset($result['Item']) || isset($result['description'])) { // 库存异步通知响应
                    $result = ['status' => true, 'data' => $result, 'message' => 'success'];
                }  else if (isset($result['status'])) { // 百胜hub报错
                    $result = ['status' => false, 'data' => $result, 'errorCode' => '', 'message' => 'BaisonHub Response Error ' . $result['message']];
                } else {
                    $result = ['status' => false, 'data' => [], 'message' => 'parse json response fail：' . $responseString];
                }

            } else {
                $result = ['status' => false, 'data' => [$responseString], 'message' => 'parse json response fail：' . $responseString];
            }
        } elseif ("xml" === $format) {
            $result = [];
            $responseBody = $responseString;
            libxml_disable_entity_loader(true);
            $decodedResponse = @simplexml_load_string($responseBody);
            if (false !== $decodedResponse) {
                $result = json_decode(json_encode($decodedResponse), true);//把里面的Object对象转乘数组
            }
            if (empty($result) && false !== strpos($responseBody, 'SOAP-ENV')) {
                $info = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $responseBody);
                $decodedResponse = @simplexml_load_string($info);
                $decodedResponse = json_decode(json_encode($decodedResponse), true);
                if ($decodedResponse) {
                    if (isset($decodedResponse['Body']['Fault'])) {
                        $result = $decodedResponse['Body']['Fault'];
                    } else {
                        $result = $decodedResponse;
                    }
                } else {
                    // throw new \Exception('Invalid XML Response');
                }
            }
            if ('SUCCESS' == data_get($result, 'Message.Code', 'FAIL')) {
                $result = ['status' => true, 'data' => $result, 'message' => $result['Message']['Description'] ?? ''];
            } else if (isset($result['errors'])) {
                $error = current($result['errors']);
                $result = ['status' => false, 'data' => $result, 'errorCode' => $error['ErrorCode'] ?? '', 'message' => $error['ErrorDescription'] ?? ''];
            } else if (isset($result['faultcode'])) { // 返回错误
                $message = $result['detail']['text'] ?? $result['faultstring'];

                $result = ['status' => false, 'data' => $result, 'message' => $message];
            } else {
                $result = ['status' => false, 'data' => [$responseBody], 'message' => 'parse xml response fail：' . (string)$responseString];
            }

            return $result;
        } else {
            throw new \RuntimeException('unknown format: ' . $format);
        }

        return $result;
    }
}
