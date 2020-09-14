<?php


namespace App\Jobs\Timer\PopTaobao;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\ItemBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoItemBatchTransferJob;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsItemRepository;
use App\Services\PlatformDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

class RdsItemBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 60;// 每 60 秒运行一次
    }

    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'taobao_rds_item_sync_jobs';

        $configServer = new PlatformDownloadConfigServer($name);
        $config = $configServer->getConfig();

        if (isset($config) && 1 == $config['stop_download']) { // 停止下载，则不再处理
            return true;
        }

        // 查询系统商店单据
        $sellerNicks = [];
        $shops = Shop::available('taobao')->get();
        foreach ($shops as $shop) {
            $sellerNicks[] = $shop['seller_nick'];
        }

        if (empty($sellerNicks)) {
            Log::info('not available shop seller nick for item rds');

            return true;
        }
        // 锁名
        $lockName = $configServer->getConfigLockCacheKey();
        // 查询页大小
        $count = isset($config['query_page_size']) && $config['query_page_size'] > 0 ? $config['query_page_size'] : 5000;
        // 任务批量大小
        $jobBatch = isset($config['job_page_size']) && $config['job_page_size'] > 0 ? $config['job_page_size'] : 500;
        // 开始时间
        $start = $configServer->getNextQueryAt(strtotime('-60 seconds'));
        $end = strtotime('-10 seconds');
        if ($start >= $end) {
            $start = strtotime('-60 seconds');
        }

        $result = true;
        /**
         * @var \Illuminate\Cache\RedisLock $lock
         */
        $lock = Cache::lock($lockName, 10 * 60);
        try {
            if ($lock->acquire()) {
                $where = [
                    ['jdp_modified', '>=', Carbon::createFromTimestamp($start)->toDateTimeString()],
                    ['jdp_modified', '<', Carbon::createFromTimestamp($end)->toDateTimeString()],
                ];
                $rds = new TaobaoRdsItemRepository();
                $total = $rds->builder()->where($where)->whereIn('nick', $sellerNicks)->count();
                Log::debug('pop taobao rds item total：' . $total, $where);
                if ($total) {
                    $result = $rds->builder()->select(['num_iid'])->where($where)->whereIn('nick', $sellerNicks)
                        ->orderBy('jdp_modified')->chunk($count, function ($results, $page) use ($where, $jobBatch) {
                            $results->pluck('num_iid')->chunk($jobBatch)->each(function ($numIids, $k) use ($page) {
                                $key = "page-$page-chunk-$k";
                                // rds 下载，下载之后直接格式化转入
                                dispatch((new ItemBatchDownloadJob(['num_iids' => $numIids, 'platform' => 'taobao', 'key' => $key]))->chain(
                                    [
                                        new TaobaoItemBatchTransferJob(['num_iids' => $numIids, 'platform' => 'taobao', 'key' => $key]),
                                    ]));
                            });
                        });
                }
                $configServer->setNextQueryAt($end);
            }
        } catch (\Exception $e) {
            Log::debug(__CLASS__ . $e->getMessage());
        } finally {
            $lock->release();
        }

        return $result;
    }
}
