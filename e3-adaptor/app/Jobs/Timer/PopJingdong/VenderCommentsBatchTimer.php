<?php


namespace App\Jobs\Timer\PopJingdong;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\VenderComments;
use App\Services\Adaptor\Jingdong\Jobs\VenderCommentsDownloadJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

class VenderCommentsBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 60;// 每 60 秒运行一次
    }

    public function isImmediate()
    {
        return true;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'jingdong_comments_sync_jobs';

        $platformConfigServer = new PlatformDownloadConfigServer($name);
        $config = $platformConfigServer->getConfig();

        if (isset($config) && 1 == $config['stop_download']) { // 停止下载，则不再处理
            return true;
        }

        // 查询系统商店单据
        $sellerNicks = [];
        $shops = Shop::available('jingdong')->get();
        foreach ($shops as $shop) {
            $sellerNicks[] = $shop['seller_nick'];
        }

        if (empty($sellerNicks)) {
            Log::info('not available shop for jingdong step trade rds');

            return true;
        }
        $result = true;

        $end = strtotime('-10 seconds');
        foreach ($shops as $shop) {
            // 店铺级别下载设置
            try {
                $configServer = new ShopDownloadConfigServer($name, $shop['code']);
            } catch (\Exception $e) {
                continue;
            }
            $config = $configServer->getConfig();
            if (isset($config) && 1 == $config['stop_download']) { // 停止下载，则不再处理
                continue;
            }
            // 锁名
            $lockName = $configServer->getConfigLockCacheKey();
            // 查询页大小
            $pageSize = isset($config['query_page_size']) && $config['query_page_size'] > 0 ? $config['query_page_size'] : 50;
            // 任务批量大小
            $jobBatch = isset($config['job_page_size']) && $config['job_page_size'] > 0 ? $config['job_page_size'] : 50;
            // 开始时间
            $start = $configServer->getNextQueryAt(strtotime('-70 seconds'));
            $end = strtotime('-10 seconds');
            if ($start >= $end) {
                $start = strtotime('-70 seconds');
            }

            $page = 1;
            $where = [
                'page'           => 1,
                'page_size'      => $pageSize,
                'start_modified' => Carbon::createFromTimestamp($start)->toDateTimeString(),
                'end_modified'   => Carbon::createFromTimestamp($end)->toDateTimeString(),
            ];

            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 10 * 60);
            try {
                if ($lock->acquire()) {
                    $commentServer = new VenderComments($shop);
                    do {
                        $comments = [];
                        $where['page'] = $page;
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
                        foreach ((array)$comments as $key => $comment) {
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
                    $configServer->setNextQueryAt($end);
                }
            } catch (\Exception $e) {
                Log::debug(__CLASS__ . $e->getMessage());
            } finally {
                $lock->release();
            }
        }

        $platformConfigServer->setNextQueryAt($end);

        return $result;
    }
}
