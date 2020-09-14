<?php


namespace App\Services\Hub\Jobs;


use App\Facades\HubClient;
use App\Jobs\Job;
use App\Models\SysStdPushConfig;
use App\Models\SysStdPushQueue;
use App\Models\SysStdPushUniqueRecord;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class SysStdPushBatchJob extends Job
{
    public $queue = 'sys_std_push_hub';

    public $ids;

    public $config;
    public $key;
    public $force;

    // 重试三次
    public $tries = 3;
    // 间隔十秒
    // public $delay = 5;
    // maxTries

    /**
     * SysStdPushAsyncBatchJob constructor.
     *
     * @param array $ids
     * @param array $config
     * @param array $key
     * @param bool $force 是否强制推送，是的话不判断状态
     */
    public function __construct($ids, $config, $key = '', $force = false)
    {
        $this->ids = $ids;
        $this->config = $config;
        $this->key = $key;
        $this->force = $force;
    }

    public function tries($tries)
    {
        $this->tries = $tries;

        return $this;
    }

    /**
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        // 如果设置了停止，则将数据释放回数据库
        if ($this->isStopPush()) {
            return [];
        }
        $start = $formatAt = Carbon::now()->toDateTimeString();
        $proxy = data_get($this->config, 'proxy', null);
        $successIds = $failIds = [];
        // 获取锁定状态单据
        $delay = data_get($this->config, 'delay', 10); // 延迟
        $requestOnce = data_get($this->config, 'request_once', 0); // 校验仅推送一次
        $queues = SysStdPushQueue::where('status', 3)->find($this->ids);
        if ($queues) {
            $method = $this->config['method'];
            if ($requestOnce) {
                $queues = $this->filterRequestOnce($queues, $method);
                if (empty($queues)) {
                    return true;
                }
            }
            $requests = [];
            foreach ($queues as $queue) {
                $requestClass = HubClient::hub($proxy)->resolveRequestClass($method);
                if ($queue->push_version > 0) {
                    $requests[$queue['id']] = $requestClass->setFormatContent($queue);
                } else {
                    $requests[$queue['id']] = $requestClass->setContent($queue->bis_id);
                }
            }
            $formatAt = Carbon::now()->toDateTimeString();
            $responses = HubClient::hub($proxy)->execute($requests);
            foreach ($responses as $key => $response) {
                if (1 == $response['status']) {
                    $successIds[] = $key;
                } else {
                    $failIds[] = $key;
                }
            }
            if (!empty($successIds)) {
                SysStdPushQueue::whereIn('id', $successIds)->update(['status' => 1]);
                if ($requestOnce) {
                    $this->createUniqueRecords($queues, $successIds);
                }
            }
            if (!empty($failIds)) {
                $this->release($delay);
            }
        }
        // \Log::debug('sys_std_push_job_trace-'.$this->key, [$this->attempts(), 'start' => $start, 'fomrat' => $formatAt, 'end' => Carbon::now()->toDateTimeString(), 'total' => count($queues), 'success_count' => count($successIds), 'fail_count' => count($failIds)]);

        return $failIds;
    }

    public function handleProxy()
    {
        $proxy = data_get($this->config, 'proxy', null);
        $successIds = $failIds = [];
        // 获取锁定状态单据
        $delay = data_get($this->config, 'delay', 10); // 延迟
        $requestOnce = data_get($this->config, 'request_once', 0); // 校验仅推送一次
        $queues = SysStdPushQueue::where('status', 3)->find($this->ids);
        if ($queues) {
            $method = $this->config['method'];
            if ($requestOnce) {
                $queues = $this->filterRequestOnce($queues, $method);
                if (empty($queues)) {
                    return true;
                }
            }
            $client = HubClient::hub($proxy);
            foreach ($queues as $queue) {
                $request = null;
                $requestClass = $client->resolveRequestClass($method);
                if ($queue->push_version > 0) {
                    $request = $requestClass->setFormatContent($queue);
                } else {
                    $request = $requestClass->setContent($queue->bis_id);
                }
                try {
                    $response = $client->execute($request);
                    if (1 == $response['status']) {
                        $successIds[] = $queue['id'];
                    } else {
                        throw new Exception('请求失败');
                    }
                } catch (Exception $e) {
                    $failIds[] = $queue['id'];
                }
            }
            if (!empty($successIds)) {
                SysStdPushQueue::whereIn('id', $successIds)->update(['status' => 1]);
                if ($requestOnce) {
                    $this->createUniqueRecords($queues, $successIds);
                }
            }
            if (!empty($failIds)) {
                $this->release($delay);
            }
        }

        return $failIds;
    }

    public function isStopPush()
    {
        if ($this->force) {
            return false;
        }
        $stop = false;
        if (1 == config('hubclient.clients.adidas.stop_push', 1)) {
            $stop = true;
        } else {
            $pushConfig = (new SysStdPushConfig())->methodMapCache($this->config['method']);
            if (1 == $pushConfig['stop_push']) {
                $stop = true;
            }
        }
        // 释放数据回队列
        if ($stop) {
            SysStdPushQueue::whereIn('id', $this->ids)->update(['status' => 0]);
        }

        return $stop;
    }

    /**
     * 过滤已经处理的队列
     *
     * @param $queues
     * @param $method
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/4 15:28
     */
    public function filterRequestOnce($queues, $method)
    {
        $queuesMap = $existsRecordMap = $pushedIdArr = [];
        // 区分不同平台，避免单号重复
        foreach ($queues as $queue) {
            if ($queue->force_push) { // 强制推送，不进行过滤
                continue;
            }
            $queuesMap[$queue['platform']][$queue['bis_id']] = $queue;
        }
        // 查询是否已经推送过
        foreach ($queuesMap as $platform => $platformQueues) {
            $exists = SysStdPushUniqueRecord::where('method', $method)
                ->where('platform', $platform)
                ->whereIn('bis_id', array_keys($platformQueues))
                ->get(['bis_id']);
            if ($exists->isNotEmpty()) {
                $existsRecordMap[$platform] = $exists;
            }
        }
        if (empty($existsRecordMap)) {
            return $queues;
        }
        // 移除已经推送的队列
        foreach ($existsRecordMap as $platform => $existsRecords) {
            foreach ($existsRecords as $existsRecord) {
                foreach ($queues as $key => $queue) {
                    // 已经处理过
                    if ($queue['bis_id'] == $existsRecord['bis_id'] && $queue['platform'] == $platform) {
                        $pushedIdArr[] = $queue['id'];
                        unset($queues[$key]);
                    }
                }
            }
        }
        // 更新已经推送的队列为成功
        if (!empty($pushedIdArr)) {
            SysStdPushQueue::whereIn('id', $pushedIdArr)->update(['status' => 1]);
        }

        return $queues;
    }

    /**
     * 新增唯一记录
     *
     * @param $queues
     * @param $ids
     *
     * @author linqihai
     * @since 2020/3/4 15:44
     */
    public function createUniqueRecords($queues, $ids)
    {
        $records = [];
        foreach ($queues as $queue) {
            if (in_array($queue['id'], $ids)) {
                $records[] = [
                    'bis_id'     => $queue['bis_id'],
                    'platform'   => $queue['platform'],
                    'method'     => $queue['method'],
                    'created_at' => Carbon::now()->toDateTimeString(),
                ];
            }
        }
        if (!empty($records)) {
            SysStdPushUniqueRecord::insertOrIgnore($records);
        }
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
        $config = $this->config;
        $queues = SysStdPushQueue::where('status', 3)->find($this->ids);
        if ($queues) {
            $retry_after = Carbon::now()->addSeconds($config['retry_after'])->timestamp;
            $ids = $queues->pluck('id');
            $data = [
                'retry_after' => $retry_after,
                'status'      => 2,
                'push_version'      => 0,
                'push_content'      => '',
                // 'extends'     => ['message' => $e->getMessage()],
            ];
            // 尝试处理三次
            DB::transaction(function () use ($ids, $data) {
                SysStdPushQueue::whereIn('id', $ids)->increment('try_times', 1, $data);
            }, 3);
        }
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['sys_std_push_hub'];
    }
}
