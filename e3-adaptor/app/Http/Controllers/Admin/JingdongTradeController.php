<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\JingdongOrderSplitAmount;
use App\Models\JingdongTrade;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 京东订单列表
 * Class JingdongTradeController
 * @package App\Http\Controllers\Admin
 */
class JingdongTradeController extends Controller
{
    /**
     * Display a listing of the 京东订单列表.
     * GET|HEAD /jingdong_trade
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($venderId = $request->get('vender_id')) {
            $where['vender_id'] = $venderId;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['vender_id'] = $shop['seller_nick'] ?? 0;
        }

        // 祖先交易号
        if ($parentOrderId = $request->get('parent_order_id')) {
            $where['parent_order_id'] = $parentOrderId;
        }

        // 父交易号
        if ($directParentOrderId = $request->get('direct_parent_order_id')) {
            $where['direct_parent_order_id'] = $directParentOrderId;
        }

        if ($state = $request->get('state')) {
            $where['state'] = $state;
        }

        if ($orderType = $request->get('order_type')) {
            $where['order_type'] = $orderType;
        }

        if ($orderId = $request->get('order_id')) {
            $where['order_id'] = $orderId;
        }

        $origin_modified = $request->get('origin_modified');
        if (isset($origin_modified[1])) {
            $where[] = ['origin_modified', '>=', strtotime($origin_modified[0])];
            $where[] = ['origin_modified', '<=', strtotime($origin_modified[1])];
        }
        $jingdongTrades = JingdongTrade::where($where)->orderByDesc('origin_modified')
                    ->paginate($request->get('perPage', 15));

        return $jingdongTrades;
    }


    /**
     * 根据条件获取指定订单
     *
     * @param Request $request
     * @return mixed
     */
    public function fetch(Request $request)
    {
        $orderId = $request->input('order_id');
        if ($venderId = $request->input('vender_id')) {
            $shop = Shop::getShopByNick($venderId);
        } else {
            $this->validate($request, ['shop_code' => 'required']);
            $shop = Shop::where('code', $request->input('shop_code'))->firstOrFail();
        }
        if ($orderId) {
            try {
                Adaptor::platform('jingdong')->download(AdaptorTypeEnum::TRADE, ['order_id' => $orderId]);
            } catch (\Exception $e) {
                Adaptor::platform('jingdong')->download('tradeApi', ['order_id' => $orderId, 'shop_code' => $shop['code']]);
            }
            $exists = JingdongOrderSplitAmount::where('order_id', $orderId)->exists();
            if (!$exists) {
                // 下载优惠明细
                $params = [
                    'order_id' => $orderId,
                    'shop_code' => $request->input('shop_code')
                ];
                Adaptor::platform('jingdong')->download(AdaptorTypeEnum::JD_ORDER_SPLIT_AMOUNT, $params);
            }
        }

        return $this->respond([]);
    }

    // 转为订单
    public function transfer(Request $request)
    {
        $orderId = $request->input('order_id');
        JingdongTrade::findOrFail($orderId);
        $result = Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::TRADE, ['order_id' => $orderId]);

        return $this->success([$result]);
    }
}
