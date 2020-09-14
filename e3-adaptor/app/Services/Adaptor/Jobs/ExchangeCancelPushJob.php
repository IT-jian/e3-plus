<?php


namespace App\Services\Adaptor\Jobs;


use App\Facades\HubClient;
use App\Models\SysStdExchange;
use App\Services\Adaptor\Exceptions\AdaptorJobException;

/**
 * 换货申请消费者主动取消或者超时关闭的，需要下发
 *
 * Class ExchangeCancelPushJob
 * @package App\Services\Adaptor\Jobs
 *
 * @author linqihai
 * @since 2020/1/12 13:56
 */
class ExchangeCancelPushJob extends BasePushJob
{
    // 重试次数
    public $tries = 5;

    // 超时时间
    public $timeout = 10;

    private $stdExchange;

    /**
     * ExchangeCancelPushJob constructor.
     * @param $stdExchange
     */
    public function __construct($stdExchange)
    {
        $this->stdExchange = $stdExchange;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $where = ['dispute_id' => $this->stdExchange['dispute_id']];
        $stdExchange = SysStdExchange::where($where)->first();
        // 校验仅同步一次
        if ($stdExchange) {
            $result = HubClient::exchangeCancel($stdExchange->toArray());
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
        return ['exchange_push'];
    }
}
