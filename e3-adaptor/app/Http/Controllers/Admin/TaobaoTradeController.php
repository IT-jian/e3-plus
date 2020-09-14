<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Models\TaobaoTrade;
use App\Services\Adaptor\AdaptorTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * 淘宝订单
 * Class TaobaoTradeController
 * @package App\Http\Controllers\Admin
 */
class TaobaoTradeController extends Controller
{
    /**
     * Display a listing of the 淘宝订单.
     * GET|HEAD /taobao_trade
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($tid = $request->get('tid')) {
            $where['tid'] = $tid;
        }

        if ($sellerNick = $request->get('seller_nick')) {
            $where['seller_nick'] = $sellerNick;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['seller_nick'] = $shop['seller_nick'] ?? '';
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        if ($type = $request->get('order_type')) {
            $where['type'] = $type;
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

        $taobaoTrades = TaobaoTrade::where($where)
            ->orderBy('origin_modified', 'desc')
            ->paginate($request->get('perPage', 15));

        return $this->respond($taobaoTrades);
    }

    /**
     * 根据条件获取指定订单
     *
     * @param Request $request
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/18 17:30
     */
    public function fetch(Request $request)
    {
        $tid = $request->input('tid');
        if ($tid) {
            if (Str::contains($tid, [','])) {
                $params = [
                    'tids' => explode(',', $tid)
                ];
            } else {
                $params = [
                    'tids' => [$tid]
                ];
            }
            Adaptor::platform('taobao')->download(AdaptorTypeEnum::TRADE, $params);
            Adaptor::platform('taobao')->transfer(AdaptorTypeEnum::TRADE_BATCH, $params);
        }

        return $this->respond([]);
    }

    // 转为订单
    public function transfer(Request $request)
    {
        $tid = $request->input('tid');
        TaobaoTrade::findOrFail($tid);
        $result = Adaptor::platform('taobao')->transfer(AdaptorTypeEnum::TRADE_BATCH, ['tids' => [$tid]]);
        return $this->success([$result]);
    }
}
