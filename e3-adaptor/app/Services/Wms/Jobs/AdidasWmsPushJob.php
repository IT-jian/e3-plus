<?php


namespace App\Services\Wms\Jobs;


use App\Facades\WmsClient;
use App\Jobs\Job;
use App\Models\AdidasWmsQueue;
use Illuminate\Support\Carbon;

/**
 * 推送 adidas 取消成功到wms
 * Class AdidasWmsPushJob
 * @package App\Services\Wms\Jobs
 */
class AdidasWmsPushJob extends Job
{
    public $queue = 'default';

    /**
     * @var array
     */
    public $bisIds;

    // 重试三次
    public $tries = 3;
    // 间隔十秒
    public $delay = 10;
    /**
     * @var string
     */
    private $method;
    // maxTries

    /**
     * AdidasWmsPushJob constructor.
     *
     * @param array $bisIds
     * @param string $method
     */
    public function __construct($bisIds, $method = 'tradeCancelSuccess')
    {
        $this->bisIds = $bisIds;
        $this->method = $method;
    }

    public function handle()
    {
        $queues = $pushWmsQueues = [];
        foreach ($this->bisIds as $bisId) {
            foreach (['baozun', 'shunfeng'] as $wms) {
                $queues[] = [
                    'bis_id' => $bisId,
                    'wms' => $wms,
                    'method' => $this->method,
                    'status' => 0,
                    'created' => Carbon::now()->toDateTimeString(),
                    'updated' => Carbon::now()->toDateTimeString(),
                ];
            }
        }
        foreach ($queues as $queue) {
            $where = [
                'bis_id' => $queue['bis_id'],
                'wms' => $queue['wms'],
                'method' => $queue['method'],
            ];
            // 新增推送队列
            $existQueue = AdidasWmsQueue::firstOrCreate($where, $queue);
            if (1 == $existQueue['status']) {
                continue;
            }
            $pushWmsQueues[$queue['wms']][] = $existQueue;
        }
        $successIds = $failIds = [];
        if ($pushWmsQueues && !$this->isStopPush()) {
            foreach ($pushWmsQueues as $wms => $queueList) {
                $requests = [];
                foreach ($queueList as $queue) {
                    $requests[$queue['id']] = WmsClient::wms($wms)->resolveRequestClass($this->method)->setContent($queue['bis_id']);
                }
                $responses = WmsClient::wms($wms)->execute($requests);
                foreach ($responses as $queueId => $response) {
                    if (isset($response['status']) && $response['status']) {
                        $successIds[] = $queueId;
                    } else {
                        $failIds[] = $queueId;
                    }
                }
            }
        }
        if ($successIds) {
            AdidasWmsQueue::whereIn('id', $successIds)->update(['status' => 1]);
        }

        if ($failIds) {
            $this->release(10);
        }

        return true;
    }

    /**
     * 全局控制是否停止推送
     *
     * @return bool
     */
    protected function isStopPush()
    {
        return 1 == config('wmsclient.stop_push', 1) ? true : false;
    }

    /**
     * 更新失败数据
     *
     * @param \Exception $e
     */
    public function failed(\Exception $e)
    {
        AdidasWmsQueue::whereIn('bis_id', $this->bisIds)->where('method', $this->method)->where('status', 0)->update(['status' => 2]);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['adidas_wms_queue'];
    }
}
