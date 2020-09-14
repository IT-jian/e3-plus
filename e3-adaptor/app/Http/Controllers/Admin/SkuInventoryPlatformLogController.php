<?php

namespace App\Http\Controllers\Admin;

use App\Models\SkuInventoryPlatformLog;
use App\Models\Sys\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 库存同步平台日志
 * Class SkuInventoryPlatformLogController
 * @package App\Http\Controllers\Admin
 */
class SkuInventoryPlatformLogController extends Controller
{
    /**
     * Display a listing of the 库存同步平台日志.
     * GET|HEAD /sku_inventory_platform_log
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

        if ($shopCode = $request->get('shop_code')) {
            $where['shop_code'] = $shopCode;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['shop_code'] = $shop['code'];
        }

        if ($batchVersion = $request->get('batch_version')) {
            $where['batch_version'] = $batchVersion;
        }

        $startAt = $request->get('start_at');
        if (isset($startAt[1])) {
            $where[] = ['start_at', '>=', $startAt[0]];
            $where[] = ['start_at', '<=', $startAt[1]];
        }
        $skuInventoryPlatformLogs = SkuInventoryPlatformLog::where($where)->orderByDesc('id')
                    ->paginate($request->get('perPage', 15));

        return $skuInventoryPlatformLogs;
    }
}
