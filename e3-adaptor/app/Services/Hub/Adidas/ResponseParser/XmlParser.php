<?php


namespace App\Services\Hub\Adidas\ResponseParser;


class XmlParser extends BaseParser implements ParserContract
{
    public function format()
    {
        // 初始化返回参数
        $this->initResponse();
        // 格式化返回数据为数组
        $decodedResponse = $this->decodedResponse();
        // 设置格式化完成的数据
        $this->setData($decodedResponse);

        if ('SUCCESS' == data_get($decodedResponse, 'Message.Code', 'FAIL')) {
            $this->setStatus(true);
            $this->setMessage($decodedResponse['Message']['Description'] ?? '');
        } else if (isset($decodedResponse['errors'])) {
            $error = current($decodedResponse['errors']);

            $this->setStatus(false);
            $this->setMessage($error['ErrorDescription'] ?? '');
            $this->setErrorCode($error['ErrorCode'] ?? '');

        } else if (isset($decodedResponse['faultcode'])) { // 返回错误
            $message = $decodedResponse['detail']['text'] ?? $decodedResponse['faultstring'];

            $this->setStatus(false);
            $this->setMessage($message);
        }

        return $this->formatResponse();
    }

    /**
     * 解析 xml
     * @return array|mixed
     */
    public function decodedResponse()
    {
        $responseString = (string)$this->response->getBody();
        libxml_disable_entity_loader(true);
        $decodedResponse = @simplexml_load_string($responseString);
        if (false !== $decodedResponse) {
            $result = json_decode(json_encode($decodedResponse), true);//把里面的Object对象转乘数组
        }
        if (empty($result) && false !== strpos($responseString, 'SOAP-ENV')) {
            $info = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $responseString);
            $decodedResponse = @simplexml_load_string($info);
            $decodedResponse = json_decode(json_encode($decodedResponse), true);
            if ($decodedResponse) {
                if (isset($decodedResponse['Body']['Fault'])) {
                    $result = $decodedResponse['Body']['Fault'];
                } else {
                    $result = $decodedResponse;
                }
            } else {
                $result = [];
            }
        }

        return $result;
    }
}