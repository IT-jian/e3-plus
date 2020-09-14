<?php


namespace App\Services\Hub\Jobs;


use App\Facades\HubClient;
use App\Jobs\Job;
use App\Models\SysStdPushQueue;
use Exception;

class SysStdPushAsyncBatchJob extends Job
{
    public $queue = 'sys_std_push_hub';

    public $ids;

    // 重试三次
    // public $tries = 3;
    // 间隔十秒
    // public $delay = 10;

    /**
     * SysStdPushAsyncBatchJob constructor.
     *
     * @param $ids
     */
    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    /**
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        $success = $fail = [];
        // 只处理锁定的数据
        $queues = SysStdPushQueue::where('status', 3)->find($this->ids, ['id', 'method', 'bis_id']);
        if (!$queues->isEmpty()) {
            foreach ($queues as $queue) {
                $request = HubClient::resolveRequestClass($queue->method);
                $request->setContent($queue->bis_id);

                $requests[$queue['id']] = $request;
            }
            $results = HubClient::execute($requests);
            foreach ($results as $key => $result) {
                if (1 == $result['status']) {
                    $success[] = $key;
                } else {
                    $fail[] = $key;
                }
            }
            if (!empty($success)) {
                SysStdPushQueue::whereIn('id', $success)->update(['status' => 1]);
            }
            /*if (!empty($fail)) {
                $result = SysStdPushQueue::whereIn('id', $fail)->update(['status' => 2]);
            }*/
            if (!empty($fail)) {
                throw new Exception(implode(',', $fail));
            }
        }
    }

    public function failed(Exception $exception)
    {
        $ids = explode(',', $exception->getMessage());
        if ($ids) { // 尝试三次后，更新为失败
            SysStdPushQueue::whereIn('id', $ids)->update(['status' => 2]);
        }
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['sys_std_push_queue'];
    }
}