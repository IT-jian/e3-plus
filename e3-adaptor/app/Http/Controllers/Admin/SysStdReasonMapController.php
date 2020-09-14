<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdReasonMap;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 原因映射
 * Class SysStdReasonMapController
 * @package App\Http\Controllers\Admin
 */
class SysStdReasonMapController extends Controller
{
    /**
     * Display a listing of the 原因映射.
     * GET|HEAD /sys_std_reason_map
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

        if ($platform = $request->get('platform')) {
            $where['platform'] = $platform;
        }

        if ($type = $request->get('type')) {
            $where['type'] = $type;
        }

        if ($sourceName = $request->get('source_name')) {
            $where['source_name'] = $sourceName;
        }

        if ($mapName = $request->get('map_name')) {
            $where['map_name'] = $mapName;
        }

        if ($remark = $request->get('remark')) {
            $where['remark'] = $remark;
        }
        $sysStdReasonMaps = SysStdReasonMap::where($where)->orderByDesc('id')
            ->paginate($request->get('perPage', 15));

        return $sysStdReasonMaps;
    }

    /**
     * Store a newly created 原因映射 in storage.
     * POST /sys_std_reason_map
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdReasonMap::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdReasonMap = SysStdReasonMap::create($input);
        SysStdReasonMap::clearMapCache();

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdReasonMap);
    }

    /**
     * Display the specified 原因映射.
     * GET|HEAD /sys_std_reason_map/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdReasonMap = SysStdReasonMap::findOrFail($id);

        return $this->respond($sysStdReasonMap);
    }

    /**
     * Update the specified 原因映射 in storage.
     * PUT/PATCH /sys_std_reason_map/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $rules = SysStdReasonMap::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdReasonMap = SysStdReasonMap::findOrFail($id);

        $sysStdReasonMap->fill($input)->save();
        SysStdReasonMap::clearMapCache();

        return $this->respond($sysStdReasonMap);
    }

    /**
     * Remove the specified 原因映射 from storage.
     * DELETE /sys_std_reason_map/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdReasonMap = SysStdReasonMap::findOrFail($id);

        $sysStdReasonMap->delete();
        SysStdReasonMap::clearMapCache();

        return $this->success([]);
    }

}
