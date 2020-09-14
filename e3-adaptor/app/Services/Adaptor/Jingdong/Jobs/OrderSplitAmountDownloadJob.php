<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

/**
 * 京东订单商品明细金额计算查询下载
 *
 * Class OrderSplitAmountDownloadJob
 * @package App\Services\Adaptor\Jingdong\Jobs
 */
class OrderSplitAmountDownloadJob extends BaseDownloadJob
{
    public $queue = 'order_split_amount_download_job';

    private $params;

    /**
     * OrderSplitAmountDownloadJob constructor.
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
        return Adaptor::platform('jingdong')->download(AdaptorTypeEnum::JD_ORDER_SPLIT_AMOUNT, $this->params);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['order_split_amount_download'];
    }
}
