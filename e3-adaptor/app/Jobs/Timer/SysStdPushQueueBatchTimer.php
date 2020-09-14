<?php


namespace App\Jobs\Timer;


use App\Models\SysStdPushConfig;
use App\Models\SysStdPushQueue;
use App\Services\Hub\Jobs\SysStdPushBatchJob;
use Cache;

class SysStdPushQueueBatchTimer extends BaseCronJob
{
    public function interval()
    {
        return 1000 * 10;// 每 10 秒运行一次
    }

    public function isImmediate()
    {
        return true;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        if ($this->isStop()) {
            return 1;
        }
        $limit = 5000;
        $name = 'sys_push_queue_batch_timer_lock';
        $version = time();
        $result = true;
        /**
         * @var \Illuminate\Cache\RedisLock $lock
         */
        $lock = Cache::lock($name, 3 * 60); // 锁定三分钟
        try {
            if ($lock->acquire()) {
                $configs = SysStdPushConfig::where('stop_push', 0)->orderBy('push_sort')->get();
                $page = 0;
                foreach ($configs as $config) {
                    do {
                        $page++;
                        $startAt = time();
                        $where = [
                            ['status', 0],
                            ['method', $config['method']],
                        ];
                        $queues = SysStdPushQueue::select(['id'])->where($where)->limit($limit)->get();
                        if ($queues->isEmpty()) {
                            break;
                        }
                        $ids = $queues->pluck('id')->toArray();
                        SysStdPushQueue::whereIn('id', $ids)->update(['status' => 3]);
                        foreach (array_chunk($ids, 50) as $key => $chunk) {
                            $job = (new SysStdPushBatchJob($chunk, $config, sprintf('%s-%s-%s', $version, $page, $key)))->tries($config['tries'])->delay($config['delay']);
                            if (isset($config['on_queue']) && !empty($config['on_queue'])) {
                                $job->onQueue($config['on_queue']);
                            }
                            dispatch($job);
                        }
                        if ($page >= 20) { // 查询 10w 数据，则跳出，等待下次查询。控制速率。
                            break;
                        }
                        // \Log::debug('push_timer', [time() - $startAt]);
                    } while (true);
                }
            }
        } catch (\Exception $e) {
            \Log::debug(__CLASS__ . $e->getMessage());
        } finally {
            $this->releaseLock($lock);
        }

        return $result;
    }

    public function isStop()
    {
        // 判断当前队列长度
        // 当前队列一直没消化，则不再推送到队列中处理
        // Queue::size('sys_std_push_queue');
        return 1 == config('hubclient.clients.adidas.stop_push', 1);
    }
}
