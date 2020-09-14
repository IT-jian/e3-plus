<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\JingdongOrderSplitAmount;
use App\Models\JingdongTrade;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\OrderSplitAmount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 京东订单金额分摊
 * Class JingdongOrderSplitAmountController
 * @package App\Http\Controllers\Admin
 */
class JingdongOrderSplitAmountController extends Controller
{
    /**
     * Display a listing of the 京东订单金额分摊.
     * GET|HEAD /jingdong_order_split_amount
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($orderId = $request->get('order_id')) {
            $where['order_id'] = $orderId;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['vender_id'] = $shop['seller_nick'] ?? 0;
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

        $jingdongOrderSplitAmounts = JingdongOrderSplitAmount::where($where)
                    ->paginate($request->get('perPage', 15));

        return $jingdongOrderSplitAmounts;
    }

    /**
     * 根据条件获取指定订单的金额明细计算数据
     *
     * @param Request $request
     * @return mixed
     */
    public function fetch(Request $request)
    {
        if ($venderId = $request->input('vender_id')) {
            $shop = Shop::getShopByNick($venderId);
        } else {
            $this->validate($request, ['shop_code' => 'required']);
            $shop = Shop::where('code', $request->input('shop_code'))->firstOrFail();
        }
        $orderId = $request->input('order_id');
        if ($orderId) {
            $params = [
                'order_id' => $orderId,
                'shop_code' => $shop['code']
            ];
            Adaptor::platform('jingdong')->download(AdaptorTypeEnum::JD_ORDER_SPLIT_AMOUNT, $params);
        }

        return $this->respond([]);
    }

    // 转为订单明细金额，重新计算
    public function transfer(Request $request)
    {
        $orderId = $request->input('order_id');
        JingdongTrade::findOrFail($orderId);
        $result = Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::JD_ORDER_SPLIT_AMOUNT, ['order_id' => $orderId]);

        return $this->success([$result]);
    }
}
