<?php


namespace App\Jobs\Timer;


use App\Models\SysStdPushConfig;
use App\Models\SysStdPushQueue;
use App\Services\Hub\Jobs\SysStdPushJob;
use Cache;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class SysStdPushQueueTimer extends CronJob
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
        $limit = 100;
        $name = 'sys_push_queue_timer_lock';

        $result = true;
        /**
         * @var \Illuminate\Cache\RedisLock $lock
         */
        $lock = Cache::lock($name, 10 * 60);
        try {
            if ($lock->acquire()) {
                $configs = SysStdPushConfig::where('stop_push', 0)->orderBy('id')->get();
                foreach ($configs as $config) {
                    $where = [
                        ['status', 0],
                        ['method', $config['method']],
                    ];
                    $exist = SysStdPushQueue::where($where)->first();
                    if ($exist) {
                        SysStdPushQueue::where($where)->orderBy('try_times')->lockForUpdate()
                            ->chunkById($limit, function ($queues) use ($config) {
                                SysStdPushQueue::whereIn('id', $queues->pluck('id'))->update(['status' => 3]);
                                foreach ($queues as $queue) {
                                    dispatch(new SysStdPushJob($queue, $config));
                                }
                            });
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::debug(__CLASS__ . $e->getMessage());
        } finally {
            $lock->release();
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