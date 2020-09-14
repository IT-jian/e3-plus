<?php


namespace App\Services\Adaptor\Taobao\Jobs\BatchDownload;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Taobao\Jobs\BaseDownloadJob;
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
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        Log::debug("job [{$this->params['key']}] running");
        $params = [
            'tids' => $this->params['tids'],
        ];
        Adaptor::platform('taobao')->download(AdaptorTypeEnum::TRADE, $params);

        Log::debug("job [{$this->params['key']}] end");
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['taobao_batch_download'];
    }
}