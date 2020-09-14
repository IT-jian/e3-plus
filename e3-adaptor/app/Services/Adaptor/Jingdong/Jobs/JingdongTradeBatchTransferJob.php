<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

class JingdongTradeBatchTransferJob extends BaseTransferJob
{
    private $params;

    /**
     * JingdongTradeBatchTransferJob constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::TRADE_BATCH, $this->params);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['jingdong_transfer_batch', 'jingdong_transfer'];
    }
}