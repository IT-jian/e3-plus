<?php


namespace App\Services\Adaptor\Taobao\Downloader;


use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Taobao\Repository\TaobaoCommentsRepository;
use Illuminate\Support\Carbon;

class CommentsDownloader implements DownloaderContract
{
    /**
     * @var TaobaoCommentsRepository
     */
    private $repository;

    public function __construct(TaobaoCommentsRepository $repository)
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
        $updateFields = ['origin_content', 'updated_at', 'sync_status'];
        if ($formatComments) {
            $this->repository->insertMulti($formatComments, $updateFields);
        }

        return true;
    }

    public function formatComment($comment)
    {
        return [
            'tid'            => $comment['tid'],
            'oid'            => $comment['oid'],
            'num_iid'        => $comment['num_iid'],
            'seller_nick'        => $comment['rated_nick'],
            'origin_content' => json_encode($comment),
            'created'        => $comment['created'],
            'created_at'     => Carbon::now()->toDateTimeString(),
            'updated_at'     => Carbon::now()->toDateTimeString(),
            'sync_status'    => 0,
        ];
    }
}
