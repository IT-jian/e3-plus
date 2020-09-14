<?php


namespace App\Services\Hub\Adidas\ResponseParser;


class CancelJsonParser extends JsonParser implements ParserContract
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

            } else { // 百胜hub报错
                parent::format();
            }
        }

        return $this->formatResponse();
    }
}