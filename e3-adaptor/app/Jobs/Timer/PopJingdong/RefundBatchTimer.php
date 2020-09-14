<?php


namespace App\Jobs\Timer\PopJingdong;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\Refund;
use App\Services\Adaptor\Jingdong\Jobs\RefundDownloadJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

/**
 * 服务单列表下载
 *
 * Class RefundBatchTimer
 * @package App\Jobs\Timer\PopJingdong
 */
class RefundBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 30;// 每 30 秒运行一次
    }

    public function isImmediate()
    {
        return true;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'jingdong_refund_sync_jobs';

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
            Log::info('not available shop seller nick  for refund timer');

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
            $start = $configServer->getNextQueryAt(strtotime('-40 seconds'));
            $end = strtotime('-10 seconds');
            if ($start >= $end) {
                $start = strtotime('-40 seconds');
            }

            $page = 1;
            $where = [
                'page'           => 1,
                'page_size'      => $pageSize,
                'start_modified' => Carbon::createFromTimestamp($start)->toDateTimeString(),
                'end_modified'   => Carbon::createFromTimestamp($end)->toDateTimeString(),
            ];
            Log::debug('refund bath timer where ', $where);
            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 60);
            try {
                if ($lock->acquire()) {
                    $refundServer = new Refund($shop);
                    $total = $refundServer->count($where);
                    Log::debug($shop['code'] . 'refund bath timer found ' . $total, $where);
                    if ($total) {
                        $totalPage = (int)ceil($total / $pageSize);
                        foreach (range(1, $totalPage) as $page) {
                            Log::debug('refund bath timer downloading ', [$page, $pageSize]);
                            $where['page'] = $page;
                            $refundTrades = $refundServer->page($where);
                            if (empty($refundTrades)) {
                                break;
                            }
                            foreach ((array)$refundTrades as $refundTrade) {
                                $refundTrade['shop'] = $shop;
                                dispatch(new RefundDownloadJob($refundTrade));
                                /*try {
                                    Adaptor::platform('jingdong')->download(AdaptorTypeEnum::REFUND, $refundTrade);
                                } catch (\Exception $e) {
                                    dispatch(new RefundDownloadJob($refundTrade));
                                }*/
                            }
                        }
                    }
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
