<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;

use App\Facades\TopClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsRefundRepository;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\RefundGetRequest;
use App\Services\Platform\Taobao\Client\Top\Request\RpRefundReviewRequest;
use App\Services\Platform\Taobao\Client\Top\Exceptions\TaobaoTopServerSideException;
use Carbon\Carbon;

/**
 * 04-同意退款 -- 审核退款
 *
 * Class RefundReturnGoodsAgreeHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/7/6 12:07
 */
class RefundReturnGoodsAgreeExtendHubApi extends BaseApi
{
    protected $notNullFields = ['refund_id', 'warehouse_status', 'shop_code'];

    public function proxy()
    {
        $request = new RpRefundReviewRequest();
        $sysStdRefund = $this->getRefund($this->data['refund_id']);
        if (empty($sysStdRefund)) {
            return $this->fail([], 'refund_id not found');
        }
        // 兼容 取消发货ag回写
        /*if (isset($this->data['deal_type']) && 2 == $this->data['deal_type']) {
            $request->setRefundPhase('onsale');
        } else {
            $request->setRefundPhase('aftersale');
        }*/
        $request->setRefundPhase($sysStdRefund['refund_phase']);
        if (isset($this->data['warehouse_status']) && '1' == $this->data['warehouse_status']) {
            $request->setResult('true');
            $request->setMessage('agree');
        } else {
            $request->setResult('false');
            $request->setMessage('refused');
        }
        $request->setOperator('adaptor');
        $request->setRefundVersion($sysStdRefund['refund_version']);
        $request->setRefundId($this->data['refund_id']);

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        if ($this->isSuccess($result)) {
            return $this->success();
        }

        if ($this->shouldRetryRequest($result)) {
            $this->createRetryPlatformRequest();
        }
        $subMsg = $result['sub_msg'] ?? '';
        // 返回正常数据
        if (!empty($subMsg) && in_array($subMsg, ['退款单已完结，无需审核'])) { // '当前退款状态不允许审核'
            return $this->success($result);
        }

        return $this->fail($result);
    }

    public function getRefund($refundId)
    {
        // 通过 api 获取最新的版本号，没获取到在从rds获取
        $request = new RefundGetRequest();
        $request = $request->setRefundId($refundId);
        $shop = Shop::getShopByCode($this->data['shop_code']);
        $refund = [];
        try {
            $response = TopClient::shop($shop['code'])->execute($request, $shop['access_token']);
            $refund = data_get($response, 'refund_get_response.refund', []);
        } catch (\Exception $e) {
            \Log::debug('find refund error' . $e->getMessage());
        }
        if (empty($refund)){
            $rds = new TaobaoRdsRefundRepository();
            $rdsRefund = $rds->getRow(['refund_id' => $refundId]);
            if (empty($rdsRefund)) {
                return [];
            }
            $originRefund = $rdsRefund->jdp_response;
            if (!is_array($originRefund)) {
                $originRefund = json_decode($originRefund, true);
            }
            $refund = data_get($originRefund, 'refund_get_response.refund', []);
        }

        return $refund;
    }

    public function isSuccess($response)
    {
        return data_get($response, 'rp_refund_review_response.is_success', false);
    }

    public function parseErrorCode($response)
    {
        $subCode = $response['sub_code'] ?? '';
        if ($subCode && in_array($subCode, ['A-AUDIT-00-16-003-01', 'isp.refund-service-unavailable', 'isp.top-remote-connection-timeout'])) {
            return parent::ERROR_CODE_RETRY;
        }

        return parent::ERROR_CODE_FAIL;
    }

    /**
     * 是否需要重试
     *
     * @param $subCode
     * @return bool
     */
    public function shouldRetryRequest($subCode)
    {
        // 退款取消审核不处理
        if (isset($this->data['deal_type']) && 2 == $this->data['deal_type']) {
            return false;
        }

        // 退货审核
        return in_array($subCode, ['A-AUDIT-00-16-002-00', 'A-AUDIT-00-16-003-01']);
    }

    /**
     * 新增重试队列
     */
    public function createRetryPlatformRequest()
    {
        $keyword = $this->data['refund_id'];
        $method = 'refundReturnGoodsAgreeExtend';
        // 校验是否正在重试
        $exist = \DB::table('retry_hub_api_request')->where('bis_id', $keyword)->where('method', $method)->exists();
        if ($exist) {
            return true;
        }
        $platform = 'taobao';
        $customer = 'adidas';
        $content = json_encode($this->data);
        $retryAt = Carbon::now()->addMinutes(30)->timestamp;
        $request = [
            'bis_id' => $keyword,
            'method' => $method,
            'platform' => $platform,
            'customer' => $customer,
            'content' => $content,
            'retry_at' => $retryAt,
            'status' => 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
        try {
            \DB::table('retry_hub_api_request')->insert($request);
        } catch (\Exception $e) {
            \Log::error('insert retry_hub_api_request fail', $request);
        }

        return true;
    }
}
