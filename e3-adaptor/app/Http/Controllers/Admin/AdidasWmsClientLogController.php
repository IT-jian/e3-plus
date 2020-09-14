<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdidasWmsClientLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * AdidasWms推送日志
 * Class AdidasWmsClientLogController
 * @package App\Http\Controllers\Admin
 */
class AdidasWmsClientLogController extends Controller
{
    /**
     * Display a listing of the AdidasWms推送日志.
     * GET|HEAD /adidas_wms_client_log
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

        if ($keyword = $request->get('keyword')) {
            $where['keyword'] = $keyword;
        }

        if ($appName = $request->get('app_name')) {
            $where['app_name'] = $appName;
        }

        if ($url = $request->get('url')) {
            $where['url'] = $url;
        }

        if ($statusCode = $request->get('status_code')) {
            $where['status_code'] = $statusCode;
        }

        $startAt = $request->get('start_at');
        if (isset($startAt[1])) {
            $where[] = ['start_at', '>=', $startAt[0]];
            $where[] = ['start_at', '<=', $startAt[1]];
        }

        $adidasWmsClientLogs = AdidasWmsClientLog::where($where)->orderByDesc('id')
                    ->paginate($request->get('perPage', 15));

        return $adidasWmsClientLogs;
    }

    /**
     * Store a newly created AdidasWms推送日志 in storage.
     * POST /adidas_wms_client_log
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = AdidasWmsClientLog::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $adidasWmsClientLog = AdidasWmsClientLog::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($adidasWmsClientLog);
    }

    /**
     * Display the specified AdidasWms推送日志.
     * GET|HEAD /adidas_wms_client_log/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $adidasWmsClientLog = AdidasWmsClientLog::findOrFail($id);

        return $this->respond($adidasWmsClientLog);
    }

    /**
     * Update the specified AdidasWms推送日志 in storage.
     * PUT/PATCH /adidas_wms_client_log/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = AdidasWmsClientLog::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $adidasWmsClientLog = AdidasWmsClientLog::findOrFail($id);

        $adidasWmsClientLog->fill($input)->save();

        return $this->respond($adidasWmsClientLog);
    }

    /**
     * Remove the specified AdidasWms推送日志 from storage.
     * DELETE /adidas_wms_client_log/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $adidasWmsClientLog = AdidasWmsClientLog::findOrFail($id);

        $adidasWmsClientLog->delete();

        return $this->success([]);
    }

}
