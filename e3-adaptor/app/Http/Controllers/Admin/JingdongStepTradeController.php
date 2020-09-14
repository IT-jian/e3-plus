<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\JingdongStepTrade;
use App\Models\JingdongTrade;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\StepTradePage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 京东预售订单
 * Class JingdongStepTradeController
 * @package App\Http\Controllers\Admin
 */
class JingdongStepTradeController extends Controller
{
    /**
     * Display a listing of the 京东预售订单.
     * GET|HEAD /jingdong_step_trade
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

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['vender_id'] = $shop['seller_nick'] ?? 0;
        }

        if ($orderId = $request->get('order_id')) {
            $where['order_id'] = $orderId;
        }

        if ($presaleId = $request->get('presale_id')) {
            $where['presale_id'] = $presaleId;
        }

        if ($shopId = $request->get('shop_id')) {
            $where['shop_id'] = $shopId;
        }

        if ($orderStatus = $request->get('order_status')) {
            $where['order_status'] = $orderStatus;
        }

        $originCreated = $request->get('origin_created');
        if (isset($originCreated[1])) {
            $where[] = ['origin_created', '>=', $originCreated[0]];
            $where[] = ['origin_created', '<=', $originCreated[1]];
        }
        $jingdongStepTrades = JingdongStepTrade::where($where)
                    ->paginate($request->get('perPage', 15));

        return $jingdongStepTrades;
    }

    /**
     * 根据条件获取指定订单
     *
     * @param Request $request
     * @return mixed
     */
    public function fetch(Request $request)
    {
        $this->validate($request, ['shop_code' => 'required']);
        $shop = Shop::where('code', $request->input('shop_code'))->firstOrFail();
        $orderId = $request->input('order_id');
        if ($orderId) {
            $api = new StepTradePage($shop);
            $trades = $api->find($orderId);
            Adaptor::platform('jingdong')->download(AdaptorTypeEnum::STEP_TRADE, $trades);
        }

        return $this->respond([]);
    }

    // 转为订单
    public function transfer(Request $request)
    {
        $orderId = $request->input('order_id');
        JingdongTrade::findOrFail($orderId);
        $result = Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::STEP_TRADE, ['order_id' => $orderId]);

        return $this->success([$result]);
    }

}
