<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sys\Shop;
use App\Models\TaobaoSkusQuantityUpdateQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 淘宝库存同步队列
 * Class TaobaoSkusQuantityUpdateQueueController
 * @package App\Http\Controllers\Admin
 */
class TaobaoSkusQuantityUpdateQueueController extends Controller
{
    /**
     * Display a listing of the 淘宝库存同步队列.
     * GET|HEAD /taobao_skus_quantity_update_queue
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($numIid = $request->get('num_iid')) {
            $where['num_iid'] = $numIid;
        }

        if ($skuId = $request->get('sku_id')) {
            $where['sku_id'] = $skuId;
        }

        if ($outerId = $request->get('outer_id')) {
            $where['outer_id'] = $outerId;
        }
        $status = $request->get('status');
        if ('' != $status) {
            $where['status'] = $status;
        }

        if ($shopCode = $request->get('shop_code')) {
            $where['shop_code'] = $shopCode;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['shop_code'] = $shop['code'];
        }

        $startAt = $request->get('start_at');
        if (isset($startAt[1])) {
            $where[] = ['start_at', '>=', $startAt[0]];
            $where[] = ['start_at', '<=', $startAt[1]];
        }
        $taobaoSkusQuantityUpdateQueues = TaobaoSkusQuantityUpdateQueue::where($where)->orderByDesc('id')
                    ->paginate($request->get('perPage', 15));

        return $taobaoSkusQuantityUpdateQueues;
    }
}
