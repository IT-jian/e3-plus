<?php

namespace App\Jobs;

use Exception;
use Log;

class TestJob extends Job
{
    public $ids;
    private $failIds;

    // 重试次数
    public $tries = 3;
    public $delay = 5;

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
        Log::debug('job ids', [$this->ids]);
        array_shift($this->ids);

        $this->failIds = ['1', '2'];
        throw new Exception(implode(',', $this->failIds));
    }

    public function failed(Exception $exception)
    {
        Log::debug('fail job', [$this->failIds, $this->ids, $exception->getMessage()]);
    }
}
