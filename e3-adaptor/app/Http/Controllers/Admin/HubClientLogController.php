<?php

namespace App\Http\Controllers\Admin;

use App\Models\HubClientLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * HubClient日志
 * Class HubClientLogController
 * @package App\Http\Controllers\Admin
 */
class HubClientLogController extends Controller
{
    /**
     * Display a listing of the HubClient日志.
     * GET|HEAD /hub_client_log
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

        if ($className = $request->get('class_name')) {
            $where['class_name'] = $className;
        }

        if ($statusCode = $request->get('status_code')) {
            $where['status_code'] = $statusCode;
        }
        $startAt = $request->get('start_at');
        if (isset($startAt[1])) {
            $where[] = ['start_at', '>=', $startAt[0]];
            $where[] = ['start_at', '<=', $startAt[1]];
        }

        $hubClientLogs = HubClientLog::where($where)->orderByDesc('id')
                    ->paginate($request->get('perPage', 15));

        return $hubClientLogs;
    }

    /**
     * Store a newly created HubClient日志 in storage.
     * POST /hub_client_log
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = HubClientLog::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $hubClientLog = HubClientLog::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($hubClientLog);
    }

    /**
     * Display the specified HubClient日志.
     * GET|HEAD /hub_client_log/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $hubClientLog = HubClientLog::findOrFail($id);

        return $this->respond($hubClientLog);
    }

    /**
     * Update the specified HubClient日志 in storage.
     * PUT/PATCH /hub_client_log/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = HubClientLog::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $hubClientLog = HubClientLog::findOrFail($id);

        $hubClientLog->fill($input)->save();

        return $this->respond($hubClientLog);
    }

    /**
     * Remove the specified HubClient日志 from storage.
     * DELETE /hub_client_log/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $hubClientLog = HubClientLog::findOrFail($id);

        $hubClientLog->delete();

        return $this->success([]);
    }

}
