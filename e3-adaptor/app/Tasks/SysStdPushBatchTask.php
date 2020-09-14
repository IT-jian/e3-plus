<?php

namespace App\Tasks;

use App\Services\Hub\Jobs\SysStdPushBatchJob;
use Hhxsv5\LaravelS\Swoole\Task\Task;

class SysStdPushBatchTask extends Task
{
    private $ids;
    private $config;

    public function __construct($ids, $config)
    {
        $this->ids = $ids;
        $this->config = $config;
    }

    // 处理任务的逻辑，运行在Task进程中，不能投递任务
    public function handle()
    {
        // 使用 task 发起调用
        $failIds = dispatch_now(new SysStdPushBatchJob($this->ids, $this->config));
        if ($failIds) {
            // 失败任务发起 redis job 重试
            dispatch((new SysStdPushBatchJob($failIds, $this->config))->tries($this->config['tries'])->delay($this->config['delay']));
        }
    }
}
