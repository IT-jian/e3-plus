<?php


namespace App\Services\Adaptor\Jobs;


use App\Facades\HubClient;
use App\Models\SysStdPushQueue;

/**
 * 待推送队列新增 -- 提前格式化好数据
 *
 * Class PushQueueCreateJob
 * @package App\Services\Adaptor\Jobs
 */
class PushQueueCreateJob extends BasePushJob
{
    // 重试次数
    public $tries = 3;

    // 超时时间
    public $timeout = 10;

    private $queues;

    /**
     * PushQueueCreateJob constructor.
     * @param $queues
     */
    public function __construct($queues)
    {
        $this->queues = $queues;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 格式化数据，记录当前版本
        foreach ($this->queues as $key => $queue) {
            $pushContent = '';
            $pushVersion = 0;
            try {
                $requestClass = HubClient::resolveRequestClass($queue['method'] );
                $requestClass->setContent($queue['bis_id'] );
                $pushContent = $requestClass->getBody();
                $pushVersion = $requestClass->getDataVersion();
            } catch (\Exception $e) {
                // 错误日志
                \Log::error($e->getMessage());
            }
            $queue['push_content'] = $pushContent;
            $queue['push_version'] = $pushVersion;
            $this->queues[$key] = $queue;
            unset($requestClass);
        }
        // 入列
        if (!empty($this->queues)) {
            SysStdPushQueue::insert($this->queues);
        }
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['push_queue_create'];
    }
}