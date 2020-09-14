<?php


namespace App\Services\Adaptor\Jobs;


use App\Models\SysStdPushQueue;
use App\Models\SysStdRefund;
use App\Services\Hub\HubRequestEnum;

/**
 * 订单取消之后判断是否推送取消
 *
 * Class CancelTradeAfterCreatePushJob
 *
 * @package App\Services\Adaptor\Jobs
 *
 * @author linqihai
 */
class CancelTradeAfterCreatePushJob extends BasePushJob
{
    // 重试次数
    public $tries = 5;

    // 超时时间
    public $timeout = 10;

    private $tids;

    /**
     * CancelTradeAfterCreatePushJob constructor.
     *
     * @param array $tids
     */
    public function __construct($tids)
    {
        $this->tids = $tids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $refunds = SysStdRefund::whereIn('tid', $this->tids)->whereNotIn('status', ['SELLER_REFUSE_BUYER', 'CLOSED'])->get(['refund_id']);
        if ($refunds->isNotEmpty()) {
            $refundIds = $refunds->pluck(['refund_id'])->toArray();
            $queues = SysStdPushQueue::whereIn('bis_id', $refundIds)->where('method', HubRequestEnum::TRADE_CANCEL)->where('status', 2)->get(['id']);
            if ($queues->isNotEmpty()) {
                $queueIds = $queues->pluck('id')->toArray();
                SysStdPushQueue::whereIn('id', $queueIds)->update(['status' => '0', 'try_times' => 0, 'retry_after' => 0]);
            }
        }
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
