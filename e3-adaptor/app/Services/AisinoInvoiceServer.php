<?php


namespace App\Services;


use App\Facades\HubApi;
use App\Facades\HubClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Taobao\Api\Invoice;

class AisinoInvoiceServer
{
    /**
     * 获取平台 API
     *
     * @param $invoiceApply
     * @return mixed
     */
    public function fetchApply($invoiceApply)
    {
        $shop = Shop::getShopByNick($invoiceApply['seller_nick']);
        return (new Invoice($shop))->find($invoiceApply['platform_tid'], $invoiceApply['apply_id']);
    }

    /**
     * 获取 omini 发票详情
     * @param $invoiceApply
     */
    public function fetchDetail($invoiceApply)
    {
        return HubClient::invoiceQuery($invoiceApply['platform_tid']);
    }

    /**
     * 请求开票
     *
     * @param $invoiceApply
     * @return array
     */
    public function invoiceCreate($invoiceApply)
    {
        $platformTid = $invoiceApply['platform_tid'] ?? '';
        if (empty($platformTid)){
            throw new \Exception('platform tid not found ');
        }
        $request = HubClient::platform('taobao')->resolveRequestClass('invoiceCreate');
        $request->setContent($invoiceApply);
        $result = HubClient::execute($request);

        return $result;
    }

    /**
     * 接口回传
     */
    public function updateDetailApi($input)
    {
        $params = [
            'method' => 'einvoiceDetailUpload',
            'content' => $input
        ];

        return HubApi::hub('adidas')->platform('taobao')->execute($params);
    }
}
