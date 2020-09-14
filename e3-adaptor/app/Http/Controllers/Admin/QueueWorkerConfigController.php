<?php

namespace App\Http\Controllers\Admin;

use App\Models\QueueWorkerConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 任务队列配置
 * Class QueueWorkerConfigController
 * @package App\Http\Controllers\Admin
 */
class QueueWorkerConfigController extends Controller
{
    /**
     * Display a listing of the 任务队列配置.
     * GET|HEAD /queue_worker_config
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($code = $request->get('code')) {
            $where['code'] = $code;
        }

        if ($name = $request->get('name')) {
            $where['name'] = $name;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }
        $queueWorkerConfigs = QueueWorkerConfig::where($where)
                    ->paginate($request->get('perPage', 15));

        return $queueWorkerConfigs;
    }

    /**
     * Store a newly created 任务队列配置 in storage.
     * POST /queue_worker_config
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = QueueWorkerConfig::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $queueWorkerConfig = QueueWorkerConfig::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($queueWorkerConfig);
    }

    /**
     * Display the specified 任务队列配置.
     * GET|HEAD /queue_worker_config/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $queueWorkerConfig = QueueWorkerConfig::findOrFail($id);

        return $this->respond($queueWorkerConfig);
    }

    /**
     * Update the specified 任务队列配置 in storage.
     * PUT/PATCH /queue_worker_config/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $rules = QueueWorkerConfig::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $queueWorkerConfig = QueueWorkerConfig::findOrFail($id);

        $queueWorkerConfig->fill($input)->save();

        return $this->respond($queueWorkerConfig);
    }

    /**
     * Remove the specified 任务队列配置 from storage.
     * DELETE /queue_worker_config/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $queueWorkerConfig = QueueWorkerConfig::findOrFail($id);

        $queueWorkerConfig->delete();

        return $this->success([]);
    }

}
