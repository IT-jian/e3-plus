<?php


namespace App\Services\Adaptor\Jingdong\Jobs\BatchDownload;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Jobs\BaseDownloadJob;
use Log;

class TradeBatchDownloadJob extends BaseDownloadJob
{
    private $params;

    /**
     * TradeBatchDownloadJob constructor.
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
        Log::debug("job [{$this->params['key']}] running");
        $params = [
            'order_ids' => $this->params['order_ids'],
        ];
        Adaptor::platform('jingdong')->download(AdaptorTypeEnum::TRADE, $params);

        Log::debug("job [{$this->params['key']}] end");
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['jingdong_batch_download'];
    }
}