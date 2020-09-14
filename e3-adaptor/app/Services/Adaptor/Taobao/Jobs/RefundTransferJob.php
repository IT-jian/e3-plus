<?php


namespace App\Services\Adaptor\Taobao\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

/**
 * 淘宝退单转入标准表
 *
 * Class RefundTransferJob
 * @package App\Services\Adaptor\Taobao\Jobs
 *
 * @author linqihai
 * @since 2020/2/28 11:52
 */
class RefundTransferJob extends BaseTransferJob
{
    private $params;

    /**
     * RefundTransferJob constructor.
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
        Adaptor::platform('taobao')->transfer(AdaptorTypeEnum::REFUND, $this->params);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['taobao_transfer'];
    }
}