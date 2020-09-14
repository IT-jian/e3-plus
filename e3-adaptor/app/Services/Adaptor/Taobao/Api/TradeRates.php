<?php


namespace App\Services\Adaptor\Taobao\Api;


use App\Facades\TopClient;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeGetRequest;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeReceiveGetRequest;
use App\Services\Platform\Taobao\Client\Top\Request\TraderatesGetRequest;

class TradeRates
{
    protected $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param $params
     * @return mixed
     *
     * @author linqihai
     * @since 2020/2/28 12:04
     */
    public function page($params)
    {
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 30;
        $start = $params['start_modified'];
        $end = $params['end_modified'];

        $request = new TraderatesGetRequest();
        $request->setPageNo($page);
        $request->setPageSize($pageSize);
        $request->setStartDate($start);
        $request->setEndDate($end);

        $list = TopClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);

        return data_get($list, 'traderates_get_response', []);
    }

    public function findByTid($tid)
    {
        $request = new TraderatesGetRequest();
        $request->setTid($tid);

        $list = TopClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);

        return data_get($list, 'traderates_get_response.trade_rates.trade_rate', []);
    }
}
