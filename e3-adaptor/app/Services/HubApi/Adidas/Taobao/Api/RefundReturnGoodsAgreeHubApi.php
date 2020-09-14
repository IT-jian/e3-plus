<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;

use App\Facades\TopClient;
use App\Models\SysStdRefund;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\NextOneLogisticsWarehouseUpdateRequest;

/**
 * 04-同意退款(收货之后同意)
 *
 * Class RefundReturnGoodsAgreeHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/1/2 10:44
 */
class RefundReturnGoodsAgreeHubApi extends BaseApi
{
    protected $notNullFields = ['refund_id', 'warehouse_status', 'shop_code'];

    public function proxy()
    {
        // 兼容 取消发货ag回写
        if (isset($this->data['deal_type']) && 2 == $this->data['deal_type']) {
            $cancelApi = new TradeSendGoodsCancelHubApi();
            if (empty($this->data['deal_code'])) {
                $refund = SysStdRefund::select(['tid'])->where('refund_id', $this->data['refund_id'])->where('platform', 'taobao')->firstOrFail();
                $this->data['deal_code'] = $refund['tid'];
            }
            $cancelApi->setData($this->data);
            $cancelApi->check();

            return $cancelApi->proxy();
        }
        $request = new NextOneLogisticsWarehouseUpdateRequest();
        $request->setRefundId($this->data['refund_id']);
        $request->setWarehouseStatus($this->data['warehouse_status']);

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'nextone_logistics_warehouse_update_response.succeed', false);
    }
}
