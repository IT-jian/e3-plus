<?php


namespace App\Services\Adaptor\Taobao\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

class ExchangeDownloadJob extends BaseDownloadJob
{
    private $exchange;

    /**
     * ExchangeDownloadJob constructor.
     *
     * @param $exchange
     */
    public function __construct($exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        Adaptor::platform('taobao')->download(AdaptorTypeEnum::EXCHANGE, $this->exchange);
        // 请求速度限制
        /*Redis::throttle('key')->allow(10)->every(60)->then(function () {
            // 任务逻辑...
        }, function () {
            // 无法获得锁...

            return $this->release(10);
        });*/
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['taobao_exchange_download'];
    }
}