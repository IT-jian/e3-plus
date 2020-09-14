<?php


namespace App\Services\Adaptor\Taobao\Api;


use App\Facades\TopClient;
use App\Services\Platform\Taobao\Client\Top\Exceptions\TaobaoTopServerSideException;
use App\Services\Platform\Taobao\Client\Top\Request\EinvoiceApplyGetRequest;

class Invoice
{
    private $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param $tid
     * @param $applyId
     * @return mixed
     */
    public function find($tid, $applyId = '')
    {
        // 查询sku详情
        $request = new EinvoiceApplyGetRequest();
        $request->setPlatformTid($tid);
        if ($applyId) {
            $request->setApplyId($applyId);
        }
        $response = TopClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);
        if (isset($response['code']) && !empty($response['code'])) {
            $code = $response['code'];
            if (!is_int($code)) {
                $code = -1;
            }
            $message = $result['msg'] ?? 'Error';
            if (isset($result['sub_msg'])) {
                $message .= ': ';
                $message .= $result['sub_msg'];
            }
            if (isset($result['sub_code'])) {
                $message .= sprintf(' (%s / %s)', $result['code'], $result['sub_code']);
            }
            $instance = new TaobaoTopServerSideException($message, $code);
            $instance->setSubErrorCode($result['sub_code'] ?? null);
            $instance->setSubErrorMessage($result['sub_msg'] ?? null);
            $instance->setResponseBody($response);

            throw $instance;
        }
        $apply = data_get($response, 'alibaba_einvoice_apply_get_response.apply_list.apply', []);

        return $apply;
    }
}
