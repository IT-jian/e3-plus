<?php


namespace App\Services\Adaptor\Jingdong\Jobs\BatchDownload;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Jobs\BaseDownloadJob;
class ItemBatchDownloadJob extends BaseDownloadJob
{
    private $params;

    /**
     * ItemBatchDownloadJob constructor.
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
        Adaptor::platform('jingdong')->download(AdaptorTypeEnum::ITEM_BATCH, $this->params);
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
