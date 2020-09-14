<?php


namespace App\Jobs\Timer\PopJingdong;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\RefundUpdate;
use App\Services\Adaptor\Jingdong\Jobs\RefundDownloadJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

/**
 * 待收货退单状态变更下载 --> 更新为 取消
 *
 * Class RefundBatchTimer
 * @package App\Jobs\Timer\PopJingdong
 */
class RefundUpdateBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 20;// 每 10 秒运行一次
    }

    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'jingdong_refund_update_sync_jobs';

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
            Log::info('not available shop seller nick for refund update timer');

            return true;
        }
        $end = strtotime('-10 seconds');
        $result = true;
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
            $start = $configServer->getNextQueryAt(strtotime('-30 seconds'));
            $end = strtotime('-10 seconds');
            if ($start >= $end) {
                $start = strtotime('-30 seconds');
            }

            $page = 1;
            $where = [
                'page'           => 1,
                'page_size'      => $pageSize,
                'start_modified' => Carbon::createFromTimestamp($start)->toDateTimeString(),
                'end_modified'   => Carbon::createFromTimestamp($end)->toDateTimeString(),
            ];

            Log::debug('refund update batch timer where ', $where);
            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 60);
            try {
                if ($lock->acquire()) {
                    $refundServer = new RefundUpdate($shop);
                    do {
                        $where['page'] = $page;
                        // 查询列表
                        $refundTrades = $refundServer->page($where);
                        Log::debug('refund update bath timer found ' . count($refundTrades));
                        if (empty($refundTrades)) {
                            break;
                        }
                        foreach ((array)$refundTrades as $refundTrade) {
                            // 校验状态，仅更新部分状态
                            if (!in_array($refundTrade['serviceStatus'], [10005, 10011, 10004, 10009, 10010, 10007, 7060, 7023, 7090])) {
                                continue;
                            }
                            $refundTrade['shop'] = $shop;
                            dispatch((new RefundDownloadJob($refundTrade))->delay(rand(10, 30)));
                            /*try {
                                Adaptor::platform('jingdong')->download(AdaptorTypeEnum::REFUND, $refundTrade);
                            } catch (\Exception $e) {
                                dispatch((new RefundDownloadJob($refundTrade))->delay(10));
                            }*/
                        }
                        $page++;
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
