<?php


namespace App\Services\Adaptor\Taobao\Api;


use App\Facades\TopClient;
use App\Services\Platform\Taobao\Client\Top\Request\RefundGetRequest;

class RefundApi
{
    protected $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    public function find($refundId)
    {
        $refundIds = $requests = [];
        if (is_array($refundId)) {
            $refundIds = $refundId;
        } else {
            $refundIds[] = $refundId;
        }
        foreach ($refundIds as $refundId) {
            $request = new RefundGetRequest();
            $requests[$refundId] = $request->setRefundId($refundId);
        }

        $response = TopClient::shop($this->shop['code'])->execute($requests, $this->shop['access_token']);

        return $response;
    }
}
