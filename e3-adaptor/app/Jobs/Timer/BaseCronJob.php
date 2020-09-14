<?php


namespace App\Jobs\Timer;


use App\Jobs\DingTalkNoticeTextSendJob;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class BaseCronJob extends CronJob
{

    public function run()
    {
    }

    /**
     * redis 解锁，重试以及告警机制
     *
     * @param \Illuminate\Cache\RedisLock $lock
     * @param string $notice
     * @return mixed
     */
    public function releaseLock($lock, $notice = '')
    {
        $counter = 1;
        $result = true;
        do {
            if ($counter > 3) {
                $params = ['message' => 'Warning: redis lock release fail!!' . $notice];
                dispatch(new DingTalkNoticeTextSendJob($params));
                break;
            }
            $result = $lock->release();
            if ($result) { // 成功则退出
                break;
            }
            // +1，休息3s再来
            $counter++;
            sleep(3);
        } while ($result);

        return $result;
    }
}
