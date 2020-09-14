<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Services\Adaptor\AdaptorTypeEnum;

/**
 * 京东评论下载
 *
 * Class VenderCommentsDownloadJob
 * @package App\Services\Adaptor\Jingdong\Jobs
 */
class VenderCommentsDownloadJob extends BaseDownloadJob
{
    private $comments;

    /**
     * VenderCommentsDownloadJob constructor.
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
        Adaptor::platform('jingdong')->download(AdaptorTypeEnum::COMMENTS, $this->comments);
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['jingdong_comments_download'];
    }
}