<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Models\SysStdRefund;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscReceiveRegisterRequest;

/**
 * 04-同意收货 -- 拆包登记
 *
 * Class RefundReturnGoodsAgreeHubApi
 *
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 */
class RefundReturnGoodsAgreeHubApi extends BaseApi
{
    public function proxy()
    {
        // 兼容取消发货回写 -- 商家退款审核
        if (isset($this->data['deal_type']) && 2 == $this->data['deal_type']) {
            $cancelApi = new TradeSendGoodsCancelHubApi();
            $cancelApi->setData($this->data);
            $cancelApi->check();

            return $cancelApi->proxy();
        }

        $params = $this->data;
        $shop = Shop::where('code', $this->data['shop_code'])->firstOrFail();
        if (empty($params['deal_code'])) {
            $refund = SysStdRefund::where('refund_id', $params['refund_id'])->firstOrFail(['tid']);
            $params['deal_code'] = $refund['tid'];
        }
        // 各个字段默认值设置：@see https://open.jd.com/home/home#/doc/common?listId=512
        $request = new AscReceiveRegisterRequest();
        $request->setBuId($shop['seller_nick']);
        $request->setServiceId($params['refund_id']);
        $request->setOrderId($params['deal_code']);
        $request->setReceivePin('adaptor');
        $request->setReceiveName('adaptor');
        $request->setPackingState($params['packing_state'] ?? 10);
        $request->setQualityState($params['quality_state'] ?? 10);
        $request->setInvoiceRecord($params['invoice_record'] ?? 10);
        $request->setJudgmentReason($params['judgment_reason'] ?? 1);
        $request->setAccessoryOrGift($params['accessory_or_gift'] ?? 1);
        $request->setAppearanceState($params['appearance_state'] ?? 10);
        $request->setReceiveRemark($params['receive_remark'] ?? 'checkin');

        $result = JosClient::shop($shop['code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'jingdong_asc_receive_register_responce.result.success', false);
    }
}
