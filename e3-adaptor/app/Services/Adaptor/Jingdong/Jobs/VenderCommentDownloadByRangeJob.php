<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\VenderComments;
use Illuminate\Support\Carbon;

class VenderCommentDownloadByRangeJob extends BaseDownloadJob
{
    private $params;

    /**
     * VenderCommentDownloadByRangeJob constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params   ;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        $pageSize = 50;
        $shop = Shop::where($this->params['shop_code'])->first();
        $where = [
            'page'           => 1,
            'page_size'      => $pageSize,
            'start_modified' => Carbon::createFromTimestamp($this->params['start_time'])->toDateTimeString(),
            'end_modified'   => Carbon::createFromTimestamp($this->params['end_time'])->toDateTimeString(),
        ];

        $commentServer = new VenderComments($shop);
        $page = 1;
        do {
            $where['page'] = $page;
            \Log::info('trade comment download start', $where);
            // 查询列表
            $page = $commentServer->page($where);
            $total = $page['totalItem'] ?? 0;
            if ($total <= 0) {
                break;
            }
            $comments = $page['comments'] ?? [];
            if (empty($comments)) {
                break;
            }
            foreach ($comments as $key => $comment) {
                $comments[$key]['vender_id'] = $shop['seller_nick'];
            }
            try {
                Adaptor::platform('jingdong')->download(AdaptorTypeEnum::COMMENTS, $comments);
            } catch (\Exception $e) {
                dispatch(new VenderCommentsDownloadJob($comments));
            }
            $totalPage = ceil($total / $pageSize);
            $page++;
            if ($page > $totalPage) {
                break;
            }
        } while (true);
        \Log::info('trade comment download end');
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
