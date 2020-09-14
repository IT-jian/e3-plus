<?php


namespace App\Jobs\Timer\PopJingdong;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Jobs\BatchDownload\TradeBatchDownloadJob;
use App\Services\Adaptor\Jingdong\Repository\Rds\JingdongRdsTradeRepository;
use App\Services\Adaptor\Jingdong\Jobs\JingdongTradeBatchTransferJob;
use App\Services\PlatformDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

class RdsTradeBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 10;// 每 10 秒运行一次
    }

    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'jingdong_rds_trade_sync_jobs';

        $configServer = new PlatformDownloadConfigServer($name);
        $config = $configServer->getConfig();

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
            Log::info('not available shop seller nick for trade rds');

            return true;
        }
        // 锁名
        $lockName = $configServer->getConfigLockCacheKey();
        // 查询页大小
        $count = isset($config['query_page_size']) && $config['query_page_size'] > 0 ? $config['query_page_size'] : 5000;
        // 任务批量大小
        $jobBatch = isset($config['job_page_size']) && $config['job_page_size'] > 0 ? $config['job_page_size'] : 500;
        // 开始时间
        $start = $configServer->getNextQueryAt(strtotime('-20 seconds'));
        $end = strtotime('-10 seconds');
        if ($start >= $end) {
            $start = strtotime('-20 seconds');
        }

        $result = true;
        /**
         * @var \Illuminate\Cache\RedisLock $lock
         */
        $lock = Cache::lock($lockName, 10 * 60);
        try {
            if ($lock->acquire()) {
                $where = [
                    ['pushModified', '>=', Carbon::createFromTimestamp($start)->toDateTimeString()],
                    ['pushModified', '<', Carbon::createFromTimestamp($end)->toDateTimeString()],
                ];
                $rds = new JingdongRdsTradeRepository();
                $total = $rds->builder()->where($where)->whereIn('venderId', $sellerNicks)->count();
                Log::debug('pop jingdong rds trade total：' . $total, $where);
                if ($total) {
                    $result = $rds->builder()->select(['orderId'])->where($where)->whereIn('venderId', $sellerNicks)
                        ->orderBy('pushModified')->chunk($count, function ($results, $page) use ($where, $jobBatch) {
                        $results->pluck('orderId')->chunk($jobBatch)->each(function ($orderIds, $k) use ($page) {
                            $key = "page-$page-chunk-$k";
                            // rds 下载，下载之后直接格式化转入
                            dispatch((new TradeBatchDownloadJob(['order_ids' => $orderIds, 'platform' => 'jingdong', 'key' => $key]))->chain(
                                [
                                    new JingdongTradeBatchTransferJob(['order_ids' => $orderIds, 'key' => $key]),
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
