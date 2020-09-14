<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdPushQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * 推送队列
 * Class SysStdPushQueueController
 * @package App\Http\Controllers\Admin
 */
class SysStdPushQueueController extends Controller
{
    /**
     * Display a listing of the 推送队列.
     * GET|HEAD /sys_std_push_queue
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

        if ($hub = $request->get('hub')) {
            $where['hub'] = $hub;
        }

        if ($method = $request->get('method')) {
            $where['method'] = $method;
        }

        if ($id = $request->get('id')) {
            $where['id'] = $id;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        if ($extends = $request->get('extends')) {
            $where['extends'] = $extends;
        }
        $query = SysStdPushQueue::where($where);
        if ($bisId = $request->get('bis_id')) {
            if (Str::contains($bisId, [','])) {
                $bisIds = explode(',', $bisId);
                $query->whereIn('bis_id', $bisIds);
            } else {
                $query->where('bis_id', $bisId);
            }
        }
        $sysStdPushQueues = $query->orderByDesc('id')
            ->paginate($request->get('perPage', 15));

        return $sysStdPushQueues;
    }

    /**
     * Store a newly created 推送队列 in storage.
     * POST /sys_std_push_queue
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdPushQueue::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdPushQueue = SysStdPushQueue::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdPushQueue);
    }

    /**
     * Display the specified 推送队列.
     * GET|HEAD /sys_std_push_queue/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdPushQueue = SysStdPushQueue::findOrFail($id);

        return $this->respond($sysStdPushQueue);
    }

    /**
     * Update the specified 推送队列 in storage.
     * PUT/PATCH /sys_std_push_queue/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $rules = SysStdPushQueue::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdPushQueue = SysStdPushQueue::findOrFail($id);

        $sysStdPushQueue->fill($input)->save();

        return $this->respond($sysStdPushQueue);
    }

    /**
     * Remove the specified 推送队列 from storage.
     * DELETE /sys_std_push_queue/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdPushQueue = SysStdPushQueue::findOrFail($id);

        $sysStdPushQueue->delete();

        return $this->success([]);
    }

}
