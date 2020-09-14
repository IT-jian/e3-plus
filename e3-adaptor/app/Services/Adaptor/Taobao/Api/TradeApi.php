<?php


namespace App\Services\Adaptor\Taobao\Api;


use App\Facades\TopClient;
use App\Services\Platform\Taobao\Client\Top\Request\TradeFullinfoGetRequest;

class TradeApi
{
    protected $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    public function find($tid)
    {
        $tids = $requests = [];
        if (is_array($tid)) {
            $tids = $tid;
        } else {
            $tids[] = $tid;
        }
        foreach ($tids as $tid) {
            $request = new TradeFullinfoGetRequest();
            $requests[$tid] = $request->setTid($tid);
        }

        $response = TopClient::shop($this->shop['code'])->execute($requests, $this->shop['access_token']);

        return $response;
    }
}
