<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

/**
 * 京东服务单更新下载
 *
 * Class RefundDownloadJob
 * @package App\Services\Adaptor\Jingdong\Jobs
 */
class RefundUpdateDownloadJob extends BaseDownloadJob
{
    private $refunds;
    private $shop;

    /**
     * RefundUpdateDownloadJob constructor.
     *
     * @param $refunds
     * @param $shop
     */
    public function __construct($refunds, $shop)
    {
        $this->refunds = $refunds;
        $this->shop = $shop;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        Adaptor::platform('jingdong')->download(AdaptorTypeEnum::REFUND_UPDATE, ['refunds' => $this->refunds, 'shop' => $this->shop]);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['jingdong_refund_download'];
    }
}