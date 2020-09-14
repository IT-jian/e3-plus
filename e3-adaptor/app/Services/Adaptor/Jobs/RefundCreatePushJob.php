<?php


namespace App\Services\Adaptor\Jobs;


use App\Facades\HubClient;
use App\Models\SysStdRefund;
use App\Services\Adaptor\Exceptions\AdaptorJobException;

/**
 * gwc
 * 订单已经发货，消费者申请退运费等部分只退款不退货的退款请求，发货之后仅退款
 *
 * Class RefundCreatePushJob
 * @package App\Services\Adaptor\Jobs
 *
 * @author linqihai
 * @since 2020/1/12 13:56
 */
class RefundCreatePushJob extends BasePushJob
{
    // 重试次数
    public $tries = 5;

    // 超时时间
    public $timeout = 10;


    private $stdRefund;

    /**
     * RefundCreatePushJob constructor.
     * @param $stdRefund
     */
    public function __construct($stdRefund)
    {
        $this->stdRefund = $stdRefund;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $where = ['refund_id' => $this->stdRefund['refund_id']];
        $stdRefund = SysStdRefund::where($where)->first();
        // 校验仅同步一次
        if ($stdRefund) {
            $result = HubClient::refundCreate($stdRefund->toArray());
            if (false == $result['status']) {
                throw new AdaptorJobException('request fail:' . $result['message']);
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
        return ['refund_push'];
    }
}
