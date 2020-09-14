<?php


namespace App\Jobs\Timer\PopTaobao;


use App\Events\DatabaseQueryExceptionEvent;
use App\Models\Sys\Shop;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\RefundBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\TradeBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\RefundBatchTransferJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsRefundRepository;
use App\Services\PlatformDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Event;
use Exception;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

/**
 * 从淘宝RDS 将需要下载的退单抛出job
 *
 * 使用 redis 锁。保证同时只有一个job在跑
 *
 * 每 35 秒查询一次，将所有单据抛出
 * Class RdsRefundBatchTimer
 * @package App\Jobs\Timer
 *
 * @author linqihai
 * @since 2020/2/28 11:57
 */
class RdsRefundBatchTimer extends CronJob
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
        $name = 'taobao_rds_refund_sync_jobs';

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
            Log::info('not available shop seller nick for refund rds');

            return true;
        }
        // 锁名
        $lockName = $configServer->getConfigLockCacheKey();
        // 查询页大小
        $count = isset($config['query_page_size']) && $config['query_page_size'] > 0 ? $config['query_page_size'] : 5000;
        // 任务批量大小
        $jobBatch = isset($config['job_page_size']) && $config['job_page_size'] > 0 ? $config['job_page_size'] : 500;
        // 开始时间
        $start = $configServer->getNextQueryAt(strtotime('-30 seconds'));
        $end = strtotime('-20 seconds');
        if ($start >= $end) {
            $start = strtotime('-30 seconds');
        }

        $result = true;
        /**
         * @var \Illuminate\Cache\RedisLock $lock
         */
        $lock = Cache::lock($lockName, 5 * 60);
        try {
            if ($lock->acquire()) {
                $where = [
                    ['jdp_modified', '>=', Carbon::createFromTimestamp($start)->toDateTimeString()],
                    ['jdp_modified', '<', Carbon::createFromTimestamp($end)->toDateTimeString()],
                ];
                $rds = new TaobaoRdsRefundRepository();
                $total = $rds->builder()->where($where)->whereIn('seller_nick', $sellerNicks)->count();
                Log::debug('pop taobao rds refund total：' . $total, $where);
                if ($total) {
                    $result = $rds->builder()->select(['refund_id', 'tid'])->where($where)->whereIn('seller_nick', $sellerNicks)->orderBy('jdp_modified')
                        ->chunk($count, function ($results, $page) use ($where, $jobBatch) {
                        $results->chunk($jobBatch)->each(function ($chunkRefunds, $k) use ($page) {
                            $tids = $chunkRefunds->pluck('tid')->toArray();
                            $refundIds = $chunkRefunds->pluck('refund_id')->toArray();
                            $key = "page-$page-chunk-$k";
                            // rds 下载，下载之后直接格式化转入
                            dispatch((new RefundBatchDownloadJob(['refund_ids' => $refundIds, 'platform' => 'taobao', 'key' => $key]))->chain(
                                [
                                    new RefundBatchTransferJob(['refund_ids' => $refundIds, 'key' => $key]),
                                ]));
                        });
                    });
                }
                $configServer->setNextQueryAt($end);
            }
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException) {
                Event::dispatch(new DatabaseQueryExceptionEvent($e));
            }
        } finally {
            $lock->release();
        }

        return $result;
    }
}
