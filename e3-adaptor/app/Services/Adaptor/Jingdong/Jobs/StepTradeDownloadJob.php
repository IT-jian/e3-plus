<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

class StepTradeDownloadJob extends BaseDownloadJob
{
    private $stepTrade;

    /**
     * StepTradeDownloadJob constructor.
     *
     * @param $stepTrade
     */
    public function __construct($stepTrade)
    {
        $this->stepTrade = $stepTrade;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        Adaptor::platform('jingdong')->download(AdaptorTypeEnum::STEP_TRADE, $this->stepTrade);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['jingdong_step_trade_download'];
    }
}