<?php


namespace App\Http\Controllers;


use App\Facades\HubApi;
use App\Services\Platform\Exceptions\PlatformServerSideException;
use Illuminate\Http\Request;
use Validator;

class ApiController extends Controller
{
    public function index(Request $request)
    {
        // 校验请求头
        $validator = Validator::make(['customer' => $request->header('customer'), 'maketplace-type' => $request->header('maketplace-type')], ['customer' => 'required', 'maketplace-type' => 'required']);
        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }
        $customer = $request->header('customer'); // 客户
        $platform = $this->platformMap($request->header('maketplace-type')); // 平台
        if (empty($platform)) {
            return $this->failed('adaptor-check-error:marketplace type not support: '.$request->header('maketplace-type'));
        }
        $content = json_decode($request->getContent(), true);

        $method = $this->methodMap($content['method']); // 执行方法
        if (empty($method)) {
            return $this->failed('adaptor-check-error:api method not support: '.$content['method']);
        }

        // 直接返回成功
        if ('production' != app()->environment()) {
            return $this->success([]);
        }

        // 模拟请求返回成功
        if (1 == $request->header('simulation')) {
            try {
                // 模拟请求
                $result = HubApi::hub($customer)->platform($platform)->mock(compact('method', 'content'));
            } catch (\Exception $e) {
                if ($e instanceof \RuntimeException) {
                    return $this->failed($e->getMessage());
                }
                return $this->failed('adaptor-error:simulation mock request fail！');
            }

            return $this->success([]);
        }

        try {
            $result = HubApi::hub($customer)->platform($platform)->execute(compact('method', 'content'));
        } catch (\Exception $e) {
            // 平台服务端异常请求
            if ($e instanceof PlatformServerSideException) {
                $response = $e->getResponseBody();
                if ($this->successMessage($response['sub_msg'] ?? '')) {
                    return $this->success($response);
                }
                $this->setSubData($response);

                return $this->failed($response['sub_msg'] ?? $e->getMessage());
            }
            if ($e instanceof \RuntimeException) {
                return $this->failed($e->getMessage());
            }
            return $this->failed('adaptor-error: request fail！' . $e->getMessage());
        }

        if (1 == $result['status']) {
            return $this->success($result['data']);
        }
        if ($result['data']) {
            $this->setSubData($result['data']);
        }

        // 500 报错需要进行重试
        $httpCode = $result['error_code'] ?? 400;

        return $this->failed($result['message'], $httpCode);
    }

    private function methodMap($method)
    {
        $map = [
            'e3plus.oms.logistics.offline.send'        => 'tradeOfflineSend', // 订单发货
            'e3plus.oms.refund.returngoods.agree'      => 'refundAgree', // 卖家同意退货
            'e3plus.oms.refund.returngoods.refuse'     => 'refundRefused', // 卖家不同意退货
            // 'e3plus.oms.ag.logistics.warehouse.update' => 'refundReturnGoodsAgree', // 同意退款(收货之后同意)
            'e3plus.oms.ag.logistics.warehouse.update' => 'refundReturnGoodsAgreeExtend', // 同意退款(收货之后同意) -- 加强
            'e3plus.oms.refund.refuse'                 => 'refundReturnGoodsRefused', // 拒绝退款(收货之后拒绝)
            'e3plus.oms.exchange.returngoods.agree'    => 'exchangeReturnGoodsAgree', // 换货同意确认收货
            'e3plus.oms.exchange.returngoods.refuse'   => 'exchangeReturnGoodsRefused', // 换货拒绝确认收货
            'e3plus.oms.items.stock.update'            => 'skusQuantityUpdate', // 库存更新
            'e3plus.oms.items.stock.async.update'      => 'skusQuantityAsyncUpdate', // 库存更新
            'e3plus.oms.items.inventory.get'           => 'itemsInventoryGet', // 店铺库存中的商品列表
            'e3plus.oms.item.sku.get'                  => 'itemSkuGet', // 查询单个sku
            'e3plus.oms.order.split'                   => 'commitOrderSplitApply', // 拆单申请
            // 'e3plus.oms.einvoice.detail.upload'        => 'einvoiceDetailUpload', // 发票详情回写平台
            'e3plus.oms.einvoice.detail.upload'        => 'alibabaEinvoiceDetailUpload', // 发票详情回写平台
        ];

        return $map[$method] ?? '';
    }

    private function platformMap($type)
    {
        $type = strtolower($type);

        $map = [
            'tmall' => 'taobao',
            'taobao' => 'taobao',
            'jingdong' => 'jingdong',
            'jd' => 'jingdong',
        ];

        return $map[$type] ?? '';
    }

    protected function successMessage($subMessage)
    {
        if (empty($subMessage)) {
            return false;
        }
        if (in_array($subMessage, ['退款单已完结，无需审核'])) {
            return true;
        }

        return false;
    }
}
