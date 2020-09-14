<?php


namespace App\Jobs\Timer;


use App\Models\SysStdPushConfig;
use App\Models\SysStdPushQueue;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class SysStdPushQueueFailTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 30;// 每 30 秒运行一次
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

        $name = 'sys_push_queue_fail_timer_lock';

        $result = true;
        /**
         * @var \Illuminate\Cache\RedisLock $lock
         */
        $lock = Cache::lock($name, 60);
        try {
            if ($lock->acquire()) {
                $configs = SysStdPushConfig::where('stop_push', 0)->orderBy('id')->get();
                foreach ($configs as $config) {
                    if ($config['try_times']) {
                        $where = [
                            ['method', $config['method']],
                            ['status', 2],
                            ['try_times', '<', $config['try_times']],
                            ['retry_after', '<', Carbon::now()->timestamp],
                        ];
                        $total = SysStdPushQueue::where($where)->first(['id']);
                        if ($total['id']) {
                            SysStdPushQueue::where($where)->update(['status' => 0]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // \Log::debug(__CLASS__ . $e->getMessage());
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
        return config('hubclient.clients.adidas.stop_push', 1);
    }
}