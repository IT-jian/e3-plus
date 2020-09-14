<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Jingdong\Client\Jos\Request\PopAfsSoaRefundapplyReplyRefundRequest;

/**
 * AG 取消发货 -- 京东退款审核
 *
 * Class TradeSendGoodsCancelHubApi
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 *
 * @author linqihai
 * @since 2020/05/22 11:45
 */
class TradeSendGoodsCancelHubApi extends BaseApi
{
    // 必填字段
    protected $notNullFields = ['warehouse_status', 'refund_id', 'shop_code'];

    public function proxy()
    {
        $request = new PopAfsSoaRefundapplyReplyRefundRequest();

        $request->setId($this->data['refund_id']);
        $request->setStatus($this->data['warehouse_status']);
        if (2 == $this->data['warehouse_status']) {
            if (isset($this->data['reject_type']) && !empty($this->data['reject_type'])) {
                $request->setRejectType($this->data['reject_type']);
            } else {
                $request->setRejectType('1');
            }
        }
        $request->setRemark($this->data['refund_remark'] ?? 'Cancel');

        $result = JosClient::shop($this->data['shop_code'])->execute($request);

        if ($this->errorToSuccess($result)) {
            return $this->success($result);
        }

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'jingdong_pop_afs_soa_refundapply_replyRefund_responce.replyResult.success', false);
    }

    public function errorToSuccess($response)
    {
        $errorMsg = data_get($response, 'jingdong_pop_afs_soa_refundapply_replyRefund_responce.replyResult.errorMsg', '');
        if (!empty($errorMsg) && in_array($errorMsg, ['申请单不能审核为该结果，请检查申请单是否已审核；'])) {
            return true;
        }

        return true;
    }
}
