<?php


namespace App\Services\Hub\Jobs;


use App\Facades\HubClient;
use App\Jobs\Job;
use App\Models\SysStdPushQueue;
use App\Models\SysStdPushUniqueRecord;
use Carbon\Carbon;
use Exception;

class SysStdPushJob extends Job
{
    public $queue = 'sys_std_push_hub';

    /**
     * @var SysStdPushQueue
     */
    public $stdPushQueue;

    public $config;

    // 重试三次
    // public $tries = 3;
    // 间隔十秒
    // public $delay = 5;
    // maxTries

    /**
     * SysStdPushAsyncBatchJob constructor.
     *
     * @param SysStdPushQueue $stdPushQueue
     */
    public function __construct(SysStdPushQueue $stdPushQueue, $config)
    {
        $this->stdPushQueue = $stdPushQueue;
        $this->config = $config;
    }

    /**
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        // 只处理锁定的数据
        $queue = $this->stdPushQueue;
        if ($queue) {
            $method = $queue->method;
            $config = $this->config;
            $requestOnce = data_get($config, 'request_once', 0); // 校验仅推送一次
            $delay = data_get($config, 'delay', 10); // 延迟
            if ($requestOnce) {
                $uniqRecord = $queue->only(['method', 'bis_id', 'platform']);
                $exists = SysStdPushUniqueRecord::where($uniqRecord)->first(['method']);
                if ($exists) { // 已经存在，则直接置为成功
                    $queue->status = 1;
                    $queue->extends = ['message' => 'request once config'];
                    $queue->save();

                    return true;
                }
                $result = HubClient::$method($queue->bis_id);
                if (1 == $result['status']) {
                    $queue->status = 1;
                    $queue->save();
                    if (!$exists) { // 不存在，则插入
                        $uniqRecord['created_at'] = Carbon::now()->toDateTimeString();
                        SysStdPushUniqueRecord::insertOrIgnore($uniqRecord);
                    }
                } else {
                    // throw new Exception('Hub Request Fail：' . $result['message']);
                    $this->release($delay);
                }
            } else {
                // 允许推送多次
                $result = HubClient::$method($queue->bis_id);
                if (1 == $result['status']) {
                    $queue->status = 1;
                    $queue->save();
                } else {
                    // throw new Exception('Hub Request Fail：' . $result['message']);
                    $this->release($delay);
                }
            }
        }

        return true;
    }

    /**
     * 处理失败
     *
     * @param Exception $e
     *
     * @author linqihai
     * @since 2020/3/2 20:28
     */
    public function failed(Exception $e)
    {
        $queue = $this->stdPushQueue;
        $config = $this->config;
        if (isset($config['try_times']) && $config['try_times'] > 0) {
            if ($queue->try_times < $config['try_times']) {
                $queue->try_times++;
                $queue->retry_after = Carbon::now()->addSeconds($config['retry_after'])->timestamp;
            }
        }
        $queue->status = 2; // 置为失败
        $queue->extends = ['message' => 'fail:' . $e->getMessage()];
        $queue->save();
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