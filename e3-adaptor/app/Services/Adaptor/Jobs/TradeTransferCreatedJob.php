<?php


namespace App\Services\Adaptor\Jobs;


use App\Facades\HubClient;
use App\Jobs\Job;
use App\Models\SysStdTrade;

/**
 * 单据新增，需要处理是否推送
 *
 * Class TradeTransferFinishJob
 * @package App\Services\Adaptor\Taobao\Jobs
 *
 * @author linqihai
 * @since 2019/12/27 15:55
 */
class TradeTransferCreatedJob extends Job
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 1. 执行转入
     */
    public function handle()
    {
        $stdTrade = SysStdTrade::where(['tid' => $this->data['tid']])->firstOrFail();
        HubClient::tradeCreate($stdTrade->toArray());
    }
}