<?php


namespace App\Services\Adaptor\Jobs;


use App\Facades\HubClient;
use App\Models\SysStdTrade;

/**
 * 订单地址更新任務
 *
 * Class TradeAddressModifyPushJob
 * @package App\Services\Adaptor\Jobs
 *
 * @author linqihai
 * @since 2020/1/12 13:56
 */
class TradeAddressModifyPushJob extends BasePushJob
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
        // 校验仅同步一次
        if ($stdTrade) {
            HubClient::tradeAddressModify($stdTrade->toArray());
        }
    }

    public function tags()
    {
        return ['trade_push'];
    }
}
