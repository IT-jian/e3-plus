<?php


namespace App\Services\Adaptor\Jobs;


use App\Jobs\Job;

class BasePushJob extends Job
{
    // 队列名称
    public $queue = 'sys_std_push_hub';

    public $delay = 10; // 重试时间

    public function failed($e)
    {
        // 异常预警处理
    }
}