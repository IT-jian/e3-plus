<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdPlatformSku;
use App\Services\Adidas\Transformer\SkuSyncTransformer;
use App\Services\PlatformSkuInventoryCsvExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 标准平台SKU
 * Class SysStdPlatformSkuController
 * @package App\Http\Controllers\Admin
 */
class SysStdPlatformSkuController extends Controller
{
    /**
     * Display a listing of the 标准平台SKU.
     * GET|HEAD /sys_std_platform_sku
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($platform = $request->get('platform')) {
            $where['platform'] = $platform;
        }

        if ($shopCode = $request->get('shop_code')) {
            $where['shop_code'] = $shopCode;
        }

        if ($shopCode = $request->get('shop')) {
            $where['shop_code'] = $shopCode;
        }

        if ($skuId = $request->get('sku_id')) {
            $where['sku_id'] = $skuId;
        }

        if ($goodsId = $request->get('num_iid')) {
            $where['num_iid'] = $goodsId;
        }

        if ($goodsName = $request->get('title')) {
            $where['title'] = $goodsName;
        }

        if ($outerId = $request->get('outer_id')) {
            $where['outer_id'] = $outerId;
        }

        $isDelete = $request->get('is_delete');
        if ('' != $isDelete) {
            $where['is_delete'] = $isDelete;
        }
        $sysStdPlatformSkus = SysStdPlatformSku::where($where)
                    ->paginate($request->get('perPage', 15));

        return $sysStdPlatformSkus;
    }

    public function push($skuId)
    {
        $stdSku = SysStdPlatformSku::where('sku_id', $skuId)->firstOrFail();

        // $result = (new RefundCreateApi())->request($stdSku->toArray());

        return $this->success($stdSku);
    }

    public function pushFormat($skuId)
    {
        $stdSku = SysStdPlatformSku::where('sku_id', $skuId)->firstOrFail();
        $result = (new SkuSyncTransformer())->format($stdSku);

        return $this->respond($result);
    }

    /**
     * Store a newly created 标准平台SKU in storage.
     * POST /sys_std_platform_sku
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdPlatformSku::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdPlatformSku = SysStdPlatformSku::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdPlatformSku);
    }

    /**
     * Display the specified 标准平台SKU.
     * GET|HEAD /sys_std_platform_sku/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdPlatformSku = SysStdPlatformSku::findOrFail($id);

        return $this->respond($sysStdPlatformSku);
    }

    /**
     * Update the specified 标准平台SKU in storage.
     * PUT/PATCH /sys_std_platform_sku/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SysStdPlatformSku::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdPlatformSku = SysStdPlatformSku::findOrFail($id);

        $sysStdPlatformSku->fill($input)->save();

        return $this->respond($sysStdPlatformSku);
    }

    /**
     * Remove the specified 标准平台SKU from storage.
     * DELETE /sys_std_platform_sku/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdPlatformSku = SysStdPlatformSku::findOrFail($id);

        $sysStdPlatformSku->delete();

        return $this->success([]);
    }

    /**
     * 导出数据到服务器上
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function export(Request $request)
    {
        $this->validate($request, ['export_date' => 'required']);

        $server = new PlatformSkuInventoryCsvExport();
        $date = $request->input('export_date');
        $shopCode = $request->input('shop_code');
        $server->exportByDate($date, $shopCode);

        return $this->respond([]);
    }

}
