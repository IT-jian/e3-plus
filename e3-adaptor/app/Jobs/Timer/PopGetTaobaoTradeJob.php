<?php

namespace App\Jobs\Timer;
use App\Jobs\TaobaoTradeDownBatchJob;
use App\Models\GetTaobaoTrade;
use App\Tasks\TaobaoTradeDownBatchTask;
use App\Tasks\TaobaoTradeDownTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
class PopGetTaobaoTradeJob extends CronJob
{
    // !!! 定时任务的`interval`和`isImmediate`有两种配置方式（二选一）：一是重载对应的方法，二是注册定时任务时传入参数。
    // --- 重载对应的方法来返回配置：开始
    public function interval()
    {
        return 10 * 1000;// 每 10 秒运行一次
    }
    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }
    // --- 重载对应的方法来返回配置：结束
    public function run()
    {
        // \Log::info(__METHOD__, ['start',  microtime(true)]);
        $limit = 500;
        $count = 0;
        do {
            $tradeList = GetTaobaoTrade::where('sync_status', 0)->limit($limit)->lockForUpdate()->get(['tid', 'rds', 'status', 'sync_status', 'jdp_modified']);
            if ($tradeList) {
                $tids_str = $tradeList->implode('tid', "','");
                \DB::update("UPDATE get_taobao_trade SET sync_status=9 WHERE tid IN ('{$tids_str}')");
                /*foreach ($tradeList as $trade) {
                    $task = new TaobaoTradeDownTask($trade->toArray());
                    $ret = Task::deliver($task, true);
                }*/
                foreach ($tradeList->pluck('tid')->chunk(500)->toArray() as $tid_arr) {
                    // $task = new TaobaoTradeDownBatchTask($tid_arr);
                    // $ret = Task::deliver($task, true);
                    // 使用任务队列
                    dispatch(new TaobaoTradeDownBatchJob($tid_arr));
                }
            }
            $count += count($tradeList);
            if (count($tradeList) < $limit) {
                break;
            }
        } while (true);
        // \Log::info(__METHOD__, ['end', $count, microtime(true)]);
        // $this->stop();
        // throw new \Exception('an exception');// 此时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }
}