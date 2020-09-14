<?php


namespace App\Services\Adaptor\Taobao\Jobs\BatchDownload;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Taobao\Jobs\BaseDownloadJob;
use Log;

class ExchangeBatchDownloadJob extends BaseDownloadJob
{
    private $params;

    /**
     * ExchangeBatchDownloadJob constructor.
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
        Log::debug("job [{$this->params['key']}] running");
        Adaptor::platform('taobao')->download(AdaptorTypeEnum::EXCHANGE, $this->params);

        Log::debug("job [{$this->params['key']}] end");
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['taobao_exchange_batch_download'];
    }
}
