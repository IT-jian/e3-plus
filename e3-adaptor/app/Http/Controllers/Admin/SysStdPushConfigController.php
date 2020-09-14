<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdPushConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 推送下发配置
 * Class SysStdPushConfigController
 * @package App\Http\Controllers\Admin
 */
class SysStdPushConfigController extends Controller
{
    /**
     * Display a listing of the 推送下发配置.
     * GET|HEAD /sys_std_push_config
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($method = $request->get('method')) {
            $where['method'] = $method;
        }

        if ($stopPush = $request->get('stop_push')) {
            $where['stop_push'] = $stopPush;
        }

        if ($proxy = $request->get('proxy')) {
            $where['proxy'] = $proxy;
        }

        if ($requestOnce = $request->get('request_once')) {
            $where['request_once'] = $requestOnce;
        }
        $sysStdPushConfigs = SysStdPushConfig::where($where)->orderBy($request->get('orderBy', 'push_sort'), $request->get('sort', 'asc'))
            ->paginate($request->get('perPage', 15));

        return $sysStdPushConfigs;
    }

    /**
     * Store a newly created 推送下发配置 in storage.
     * POST /sys_std_push_config
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdPushConfig::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();
        if (empty($input['proxy'])) {
            $input['proxy'] = null;
        }
        $sysStdPushConfig = SysStdPushConfig::create($input);

        SysStdPushConfig::clearMapCache();

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdPushConfig);
    }

    /**
     * Display the specified 推送下发配置.
     * GET|HEAD /sys_std_push_config/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdPushConfig = SysStdPushConfig::findOrFail($id);

        return $this->respond($sysStdPushConfig);
    }

    /**
     * Update the specified 推送下发配置 in storage.
     * PUT/PATCH /sys_std_push_config/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $rules = SysStdPushConfig::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();
        if (empty($input['proxy'])) {
            $input['proxy'] = null;
        }
        $sysStdPushConfig = SysStdPushConfig::findOrFail($id);

        $sysStdPushConfig->fill($input)->save();

        SysStdPushConfig::clearMapCache();

        return $this->respond($sysStdPushConfig);
    }

    /**
     * Remove the specified 推送下发配置 from storage.
     * DELETE /sys_std_push_config/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdPushConfig = SysStdPushConfig::findOrFail($id);

        $sysStdPushConfig->delete();

        SysStdPushConfig::clearMapCache();

        return $this->success([]);
    }

}
