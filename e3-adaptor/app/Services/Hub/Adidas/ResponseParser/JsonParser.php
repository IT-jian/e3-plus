<?php


namespace App\Services\Hub\Adidas\ResponseParser;


class JsonParser extends BaseParser implements ParserContract
{
    public function format()
    {
        // 初始化返回参数
        $this->initResponse();
        // 格式化返回数据为数组
        $decodedResponse = $this->decodedResponse();
        // 设置格式化完成的数据
        $this->setData($decodedResponse);

        if (null !== $decodedResponse) {
            $result = $decodedResponse;
            if ($firstResult = data_get($result, 'OrderLines.OrderLine.0', [])) {
                $status = true;
                $message = $firstResult['responseMessage'] ?? '';
                if (isset($firstResult['Error'])) {
                    $status = false;
                    $message = $firstResult['Error'];
                }
                $this->setStatus($status);
                $this->setMessage($message);

            } else if (isset($result['ResponseCode'])) { // 标准格式返回成功和失败
                $status = true;
                if ('FAIL' == $result['ResponseCode']) {
                    $status = false;
                }
                $message = $result['ResponseMessage'] ?? '';

                $this->setStatus($status);
                $this->setMessage($message);

            } else if (isset($result['errors'])) {
                $error = current($result['errors']);

                $this->setStatus(false);
                $this->setMessage($error['ErrorDescription'] ?? '');
                $this->setErrorCode($error['ErrorCode'] ?? '');

            } else if (isset($result['status'])) { // 百胜hub报错
                $this->setStatus(false);
                $this->setMessage('BaisonHub Response Error ' . $result['message']);
            }
        }

        return $this->formatResponse();
    }

    public function decodedResponse()
    {
        $responseString = (string)$this->response->getBody();
        $decodedResponse = json_decode($responseString, true);

        return $decodedResponse;
    }
}