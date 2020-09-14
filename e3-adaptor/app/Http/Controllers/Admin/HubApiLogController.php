<?php

namespace App\Http\Controllers\Admin;

use App\Models\HubApiLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * HubApi日志
 * Class HubApiLogController
 * @package App\Http\Controllers\Admin
 */
class HubApiLogController extends Controller
{
    /**
     * Display a listing of the HubApi日志.
     * GET|HEAD /hub_api_log
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
        $hubApiLogs = HubApiLog::where($where)->orderByDesc('id')
                    ->paginate($request->get('perPage', 15));

        return $hubApiLogs;
    }

    /**
     * Store a newly created HubApi日志 in storage.
     * POST /hub_api_log
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = HubApiLog::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $hubApiLog = HubApiLog::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($hubApiLog);
    }

    /**
     * Display the specified HubApi日志.
     * GET|HEAD /hub_api_log/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $hubApiLog = HubApiLog::findOrFail($id);

        return $this->respond($hubApiLog);
    }

    /**
     * Update the specified HubApi日志 in storage.
     * PUT/PATCH /hub_api_log/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = HubApiLog::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $hubApiLog = HubApiLog::findOrFail($id);

        $hubApiLog->fill($input)->save();

        return $this->respond($hubApiLog);
    }

    /**
     * Remove the specified HubApi日志 from storage.
     * DELETE /hub_api_log/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $hubApiLog = HubApiLog::findOrFail($id);

        $hubApiLog->delete();

        return $this->success([]);
    }

}
