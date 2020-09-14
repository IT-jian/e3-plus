<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Repository\JingdongCommentsRepository;
use Illuminate\Support\Carbon;

/**
 * 商品评论下载
 * Class CommentsDownloader
 * @package App\Services\Adaptor\Jingdong\Downloader
 */
class CommentsDownloader implements DownloaderContract
{
    private $repository;

    public function __construct(JingdongCommentsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function download($comments)
    {
        return $this->saveComments($comments);
    }

    public function saveComments($comments)
    {
        $formatComments = [];
        foreach ($comments as $comment) {
            $formatComments[] = $this->formatComment($comment);
        }
        $updateFields = ['origin_content', 'origin_modified', 'updated_at'];
        if ($formatComments) {
            $this->repository->insertMulti($formatComments, $updateFields);
        }

        return true;
    }

    public function formatComment($comment)
    {
        return [
            'comment_id' => $comment['commentId'],
            'order_id' => $comment['orderId'],
            'vender_id' => $comment['vender_id'],
            'sku_id' => $comment['skuid'],
            'origin_content' => json_encode($comment),
            'origin_created' => time(),
            'origin_modified' => time(),
            'creation_time' => Carbon::createFromTimestampMs($comment['creationTime'])->toDateTimeString(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
