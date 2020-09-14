<?php


namespace App\Services\Adaptor\Taobao\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

class TradeCommentDownloadJob extends BaseDownloadJob
{
    private $comments;

    /**
     * TradeCommentDownloadJob constructor.
     *
     * @param $comments
     */
    public function __construct($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        Adaptor::platform('taobao')->download(AdaptorTypeEnum::COMMENTS, $this->comments);
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
        return ['taobao_comments_download'];
    }
}
