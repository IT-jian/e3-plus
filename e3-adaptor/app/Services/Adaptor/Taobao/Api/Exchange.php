<?php


namespace App\Services\Adaptor\Taobao\Api;


use App\Facades\TopClient;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeGetRequest;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeReceiveGetRequest;

class Exchange
{
    private $shop;

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

        $request = new ExchangeReceiveGetRequest();
        $fields = 'dispute_id,alipay_no,status,modified,created';
        $request = $request->setFields($fields);
        $request->setPageNo($page);
        $request->setPageSize($pageSize);
        $request->setStartGmtModifiedTime($start);
        $request->setEndGmtModifedTime($end);

        $list = TopClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);

        return data_get($list, 'tmall_exchange_receive_get_response.results.exchange', []);
    }

    /**
     * 换货单详情
     *
     * @param $disputeId
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/30 13:27
     */
    public function detail($disputeId)
    {
        $request = new ExchangeGetRequest();
        $request->setDisputeId($disputeId);

        $originExchange = TopClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);

        return $originExchange;
    }

    /**
     * 并发请求
     *
     * @param $disputeIdArr
     * @return array|mixed
     *
     * @author linqihai
     * @since 2020/1/10 16:34
     */
    public function detailMulti($disputeIdArr)
    {
        $requests = [];
        foreach ($disputeIdArr as $disputeId) {
            $request = new ExchangeGetRequest();
            $request->setDisputeId($disputeId);
            $requests[$disputeId] = $request;
        }

        return TopClient::shop($this->shop['code'])->execute($requests, $this->shop['access_token']);
    }
}
