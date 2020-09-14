<?php


namespace App\Jobs\Timer\PopJingdong;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Api\RefundApplyQuery;
use App\Services\Adaptor\Jingdong\Jobs\RefundApplyDownloadJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

/**
 * 退款单申请审核时间-增量查询下载
 *
 * Class RefundBatchTimer
 * @package App\Jobs\Timer\PopJingdong
 */
class RefundApplyCheckBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 30;// 每 30 秒运行一次
    }

    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'jingdong_refund_apply_check_sync_jobs';

        $platformConfigServer = new PlatformDownloadConfigServer($name);
        $config = $platformConfigServer->getConfig();
        if (isset($config) && 1 == $config['stop_download']) { // 停止下载，则不再处理
            return true;
        }

        // 查询系统商店单据
        $sellerNicks = [];
        $shops = Shop::available('jingdong')->get()->toArray();
        foreach ($shops as $shop) {
            $sellerNicks[] = $shop['seller_nick'];
        }

        if (empty($sellerNicks)) {
            Log::info('not available shop seller nick for refund apply timer');

            return true;
        }
        $result = true;
        // 与退款申请时间查询下载错开
        $end = strtotime('-30 seconds');
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
            $start = $configServer->getNextQueryAt(strtotime('-60 seconds'));
            $end = strtotime('-30 seconds');
            if ($start >= $end) {
                $start = strtotime('-60 seconds');
            }

            $page = 1;
            $where = [
                'page'           => 1,
                'page_size'      => $pageSize,
                'start_modified' => Carbon::createFromTimestamp($start)->toDateTimeString(),
                'end_modified'   => Carbon::createFromTimestamp($end)->toDateTimeString(),
            ];
            // Log::debug('refund apply bath timer where ', $where);
            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 10 * 60);
            try {
                if ($lock->acquire()) {
                    $refundServer = new RefundApplyQuery($shop);
                    do {
                        $where['page'] = $page;
                        // 查询列表 -- 按照审核时间查询
                        $refundApplies = $refundServer->pageByCheck($where);
                        if (empty($refundApplies)) {
                            break;
                        }
                        Log::debug('refund apply bath check timer found ' . count($refundApplies));
                        foreach ((array)$refundApplies as $key => $refundApply) {
                            $refundApplies[$key]['vender_id'] = $shop['seller_nick'];
                        }
                        dispatch(new RefundApplyDownloadJob($refundApplies));
                        $page++;
                        if (100 == $page) { // 该接口仅允许查询100页，重新设置查询时间
                            $lastRefund = array_pop($refundApplies);
                            $where['page'] = $page = 1;
                            $where['start_modified'] = $lastRefund['applyTime'];
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
