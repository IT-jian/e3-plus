<?php

namespace App\Http\Controllers\Admin;

use App\Models\SkuInventoryApiLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 库存更新日志
 * Class SkuInventoryApiLogController
 * @package App\Http\Controllers\Admin
 */
class SkuInventoryApiLogController extends Controller
{
    /**
     * Display a listing of the 库存更新日志.
     * GET|HEAD /sku_inventory_api_log
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($apiMethod = $request->get('api_method')) {
            $where['api_method'] = $apiMethod;
        }

        if ($ip = $request->get('ip')) {
            $where['ip'] = $ip;
        }

        if ($requestId= $request->get('request_id')) {
            $where['request_id'] = $requestId;
        }
        if ($requestId= $request->filled('keyword')) {
            $where[] = ['input', 'like', "%{$request->get('keyword')}%"];
        }

        if ($partner = $request->get('partner')) {
            $where['partner'] = $partner;
        }

        if ($startAt = $request->get('start_at')) {
            $where[] = ['start_at', '>=', $startAt[0]];
            $where[] = ['start_at', '<=', $startAt[1]];
        }

        if ($responseStatus = $request->get('response_status')) {
            $where['response_status'] = $responseStatus;
        }
        $skuInventoryApiLogs = SkuInventoryApiLog::where($where)->orderByDesc('id')
                    ->paginate($request->get('perPage', 15));

        return $skuInventoryApiLogs;
    }

    /**
     * Store a newly created 库存更新日志 in storage.
     * POST /sku_inventory_api_log
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SkuInventoryApiLog::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $skuInventoryApiLog = SkuInventoryApiLog::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($skuInventoryApiLog);
    }

    /**
     * Display the specified 库存更新日志.
     * GET|HEAD /sku_inventory_api_log/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $skuInventoryApiLog = SkuInventoryApiLog::findOrFail($id);

        return $this->respond($skuInventoryApiLog);
    }

    /**
     * Update the specified 库存更新日志 in storage.
     * PUT/PATCH /sku_inventory_api_log/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SkuInventoryApiLog::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $skuInventoryApiLog = SkuInventoryApiLog::findOrFail($id);

        $skuInventoryApiLog->fill($input)->save();

        return $this->respond($skuInventoryApiLog);
    }

    /**
     * Remove the specified 库存更新日志 from storage.
     * DELETE /sku_inventory_api_log/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $skuInventoryApiLog = SkuInventoryApiLog::findOrFail($id);

        $skuInventoryApiLog->delete();

        return $this->success([]);
    }

}
