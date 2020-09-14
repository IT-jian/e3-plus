<?php


namespace App\Services\Adaptor\Taobao\Listeners;


use App\Models\SysStdPushQueue;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Jobs\PushQueueCreateJob;
use App\Services\Hub\PushQueueFormatType;
use Carbon\Carbon;
use Exception;
use Log;

trait TaobaoPushQueueTrait
{
    public function formatQueue($key, $method, $status = 0, $extends = [])
    {
        return [
            'bis_id'     => $key,
            'platform'   => 'taobao',
            'hub'        => 'adidas',
            'method'     => $method,
            'status'     => $status,
            'extends'    => json_encode($extends),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * 强制推送队列
     * @param $key
     * @param $method
     * @param int $status
     * @param array $extends
     * @return array
     *
     * @author linqihai
     * @since 2020/3/11 18:19
     */
    public function formatForceQueue($key, $method, $status = 0, $extends = [])
    {
        $extends['force_push'] = 1;

        return $this->formatQueue($key, $method, $status, $extends);
    }

    /**
     * 入列
     *
     * @param $data
     * @return bool
     *
     * @author linqihai
     * @since 2020/3/4 14:45
     */
    public function pushQueue($data)
    {
        if (PushQueueFormatType::is(PushQueueFormatType::WHEN_PUSH_TO_QUEUE)) {
            dispatch(new PushQueueCreateJob($data));
            return true;
        }
        return SysStdPushQueue::insert($data);
    }

    public function queueLock($ids)
    {
        return SysStdPushQueue::whereIn('id', $ids)->update(['status' => 3]);
    }

    public function queueSuccess($ids)
    {
        return SysStdPushQueue::whereIn('id', $ids)->update(['status' => 1]);
    }

    public function queueFail($ids)
    {
        return SysStdPushQueue::whereIn('id', $ids)->update(['status' => 2]);
    }

    public function popQueue()
    {

    }

    /**
     * 如果已经存在，并且还没执行，则移除
     * @param $queue
     *
     * @author linqihai
     * @since 2020/3/11 17:33
     */
    public function popIfExist($queue)
    {
        $where = [
            ['method', $queue['method']],
            ['platform', 'taobao'],
            ['bis_id', $queue['bis_id']],
        ];
        try {
            SysStdPushQueue::whereIn('status', [0, 2])->where($where)->delete();
        } catch (Exception $e) {
            Log::info('delete queue fail', $queue);
        }
    }

    /**
     * 如果还未推送，需要移除
     *
     * @param array $queue
     * @param int $status 设置状态为已经处理
     * @param array $extends 扩展属性
     */
    public function popBeforePush($queue, $status = 1, $extends = [])
    {
        $where = [
            ['method', $queue['method']],
            ['platform', 'taobao'],
            ['bis_id', $queue['bis_id']],
        ];

        try {
            SysStdPushQueue::whereIn('status', [0, 2])->where($where)->update(['status' => $status, 'extends' => $extends]);
        } catch (Exception $e) {
            Log::info('delete queue fail', $queue);
        }
    }
}
