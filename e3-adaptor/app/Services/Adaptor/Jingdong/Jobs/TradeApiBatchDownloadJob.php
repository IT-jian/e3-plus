<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;

/**
 * 京东订单通过API下载
 *
 * Class TradeApiBatchDownloadJob
 * @package App\Services\Adaptor\Jingdong\Jobs
 */
class TradeApiBatchDownloadJob extends BaseDownloadJob
{
    private $params;
    /**
     * TradeApiBatchDownloadJob constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        Adaptor::platform('jingdong')->download('tradeApiBatch', $this->params);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['jingdong_api_batch_download'];
    }
}
