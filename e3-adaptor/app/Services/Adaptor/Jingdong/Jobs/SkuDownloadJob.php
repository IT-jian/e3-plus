<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

/**
 * 京东平台SKU下载
 *
 * Class SkuDownloadJob
 * @package App\Services\Adaptor\Jingdong\Jobs
 */
class SkuDownloadJob extends BaseDownloadJob
{
    private $params;
    /**
     * SkuDownloadJob constructor.
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
        Adaptor::platform('jingdong')->download(AdaptorTypeEnum::SKU, $this->params);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['jingdong_sku_download'];
    }
}