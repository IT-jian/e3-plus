<?php


namespace App\Services\Adaptor\Jobs;


use App\Facades\HubClient;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Exceptions\AdaptorJobException;
use Exception;

/**
 * 订单推送job
 *
 * Class TradeCreatePushJob
 * @package App\Services\Adaptor\Jobs
 *
 * @author linqihai
 * @since 2020/1/12 13:56
 */
class TradeCreatePushJob extends BasePushJob
{
    // 重试次数
    public $tries = 5;

    // 超时时间
    public $timeout = 10;

    private $stdTrade;

    /**
     * TradeCreatePushJob constructor.
     * @param $stdTrade
     */
    public function __construct($stdTrade)
    {
        $this->stdTrade = $stdTrade;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $where = ['tid' => $this->stdTrade['tid']];
        $stdTrade = SysStdTrade::where($where)->first();
        if ($stdTrade) {
            // 校验仅同步一次
            $result = HubClient::tradeCreate($stdTrade->toArray());
            if (false == $result['status']) {
                throw new AdaptorJobException('request fail:' . $result['message']);
            }
        }
    }

    public function failed(Exception $exception)
    {
        // 失败处理事件
        \Log::error(__CLASS__ . ' run failed' . $exception->getMessage());
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['trade_push'];
    }
}
