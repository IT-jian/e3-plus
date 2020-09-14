<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

/**
 * 京东服务单批量
 *
 * Class RefundBatchDownloadJob
 * @package App\Services\Adaptor\Jingdong\Jobs
 */
class RefundBatchDownloadJob extends BaseDownloadJob
{
    private $params;
    /**
     * RefundBatchDownloadJob constructor.
     *
     * @param $params ['refund_ids' => [], 'shop_code' => '']
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
        Adaptor::platform('jingdong')->download(AdaptorTypeEnum::REFUND_BATCH, $this->params);
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
