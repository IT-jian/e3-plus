<?php


namespace App\Services\Adaptor\Jobs;


use App\Facades\Adaptor;
use App\Jobs\Job;
use App\Services\Adaptor\AdaptorTypeEnum;

/**
 * 单据转入完成，需要调用转入
 *
 * Class TradeDownloadFinishJob
 * @package App\Services\Adaptor\Taobao\Jobs
 *
 * @author linqihai
 * @since 2019/12/27 15:54
 */
class TradeDownloadFinishJob extends Job
{
    private $data;

    /**
     * TradeDownloadFinishJob constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 1. 执行转入
     */
    public function handle()
    {
        Adaptor::platform($this->data['platform'])->transfer(AdaptorTypeEnum::TRADE, ['tid' => $this->data['tid']]);
    }
}