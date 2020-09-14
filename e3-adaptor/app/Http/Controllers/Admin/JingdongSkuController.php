<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\JingdongSku;
use App\Services\Adaptor\AdaptorTypeEnum;
use Illuminate\Http\Request;

/**
 * 京东平台SKU
 * Class JingdongSkuController
 * @package App\Http\Controllers\Admin
 */
class JingdongSkuController extends Controller
{
    /**
     * Display a listing of the 京东平台SKU.
     * GET|HEAD /jingdong_sku
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

        if ($shopCode = $request->get('shop_code')) {
            $where['shop_code'] = $shopCode;
        }

        if ($sku_id = $request->get('sku_id')) {
            $where['sku_id'] = $sku_id;
        }

        if ($outerId = $request->get('outer_id')) {
            $where['outer_id'] = $outerId;
        }

        if ($wareId = $request->get('ware_id')) {
            $where['ware_id'] = $wareId;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        $created = $request->get('created');
        if (isset($created[1])) {
            $where[] = ['created', '>=', $created[0]];
            $where[] = ['created', '<=', $created[1]];
        }

        $modified = $request->get('modified');
        if (isset($modified[1])) {
            $where[] = ['modified', '>=', $modified[0]];
            $where[] = ['modified', '<=', $modified[1]];
        }
        $jingdongSkus = JingdongSku::where($where)
                    ->paginate($request->get('perPage', 15));

        return $jingdongSkus;
    }

    public function fetch(Request $request)
    {
        $this->validate($request, ['shop_code' => 'required', 'sku_id' => 'required']);
        $skuId = $request->input('sku_id');
        if ($skuId) {
            Adaptor::platform('jingdong')->download(AdaptorTypeEnum::SKU, ['shop_code' => $request['code'], 'sku_id' => $skuId]);
        } else {
            $this->failed('Invalid Params');
        }

        return $this->respond([]);
    }

}
