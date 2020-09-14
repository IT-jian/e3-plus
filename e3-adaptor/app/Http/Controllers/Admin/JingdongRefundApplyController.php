<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\JingdongRefundApply;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\RefundApplyQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 京东退款申请
 * Class JingdongRefundApplyController
 * @package App\Http\Controllers\Admin
 */
class JingdongRefundApplyController extends Controller
{
    /**
     * Display a listing of the 京东退款申请.
     * GET|HEAD /jingdong_refund_apply
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($id = $request->get('id')) {
            $where['id'] = $id;
        }
        if ($venderId = $request->get('vender_id')) {
            $where['vender_id'] = $venderId;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['vender_id'] = $shop['seller_nick'] ?? 0;
        }

        if ($orderId = $request->get('order_id')) {
            $where['order_id'] = $orderId;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        if ($reason = $request->get('reason')) {
            $where['reason'] = $reason;
        }

        $originCreated = $request->get('origin_created');
        if (isset($originCreated[1])) {
            $where[] = ['origin_created', '>=', $originCreated[0]];
            $where[] = ['origin_created', '<=', $originCreated[1]];
        }

        $originUpdated = $request->get('origin_updated');
        if (isset($originUpdated[1])) {
            $where[] = ['origin_updated', '>=', $originUpdated[0]];
            $where[] = ['origin_updated', '<=', $originUpdated[1]];
        }

        $jingdongRefundApplies = JingdongRefundApply::where($where)
                    ->paginate($request->get('perPage', 15));

        return $jingdongRefundApplies;
    }

    public function fetch(Request $request)
    {
        if ($venderId = $request->input('vender_id')) {
            $shop = Shop::getShopByNick($venderId);
        } else {
            $this->validate($request, ['shop_code' => 'required']);
            $shop = Shop::where('code', $request->input('shop_code'))->firstOrFail();
        }
        $id = $request->input('id');
        $order_id = $request->input('order_id');
        if ($id || $order_id) {
            $refundApi = new RefundApplyQuery($shop);
            $refunds = $refundApi->find(['id' => $id, 'order_id' => $order_id]);
            foreach ($refunds as $key => $refund) {
                $refund['vender_id'] = $shop['seller_nick'];
                $refunds[$key] = $refund;
            }
            Adaptor::platform('jingdong')->download(AdaptorTypeEnum::REFUND_APPLY, $refunds);
        } else {
            $this->failed('Invalid Params');
        }

        return $this->respond([]);
    }
}
