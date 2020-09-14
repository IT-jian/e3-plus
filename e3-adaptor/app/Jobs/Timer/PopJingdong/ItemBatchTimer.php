<?php


namespace App\Jobs\Timer\PopJingdong;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\ItemTotal;
use App\Services\Adaptor\Jingdong\Jobs\BatchDownload\ItemBatchDownloadJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

class ItemBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 90;// 每 90 秒运行一次
    }

    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'jingdong_item_sync_jobs';

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
            Log::info('not available shop for jingdong step trade rds');

            return true;
        }
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
            $start = $configServer->getNextQueryAt(strtotime('-100 seconds'));
            $end = strtotime('-10 seconds');
            if ($start >= $end) {
                $start = strtotime('-100 seconds');
            }

            $endTemp = 0;
            $timeRange = [];
            // 如果超过了 30 分钟，则划分每 30 分钟下载一次
            do {
                $endTemp = $start + 1800;
                if ($endTemp > $end) {
                    $endTemp = $end+1;
                }
                $timeRange[] = [
                    'start' => $start,
                    'end' => $endTemp,
                ];
                $start = $endTemp;
            } while ($end > $start);

            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 3 * 60);
            try {
                if ($lock->acquire()) {
                    foreach ($timeRange as $item) {
                        $where = [
                            'page'           => 1,
                            'page_size'      => $pageSize,
                            'start_modified' => Carbon::createFromTimestamp($item['start'])->toDateTimeString(),
                            'end_modified'   => Carbon::createFromTimestamp($item['end'])->toDateTimeString(),
                            'shop_code'   => $shop['code'],
                        ];
                        // 查询总数，分页在job中查询下载
                        $skuServer = new ItemTotal($shop);
                        $total = $skuServer->count($where);
                        if ($total) {
                            $totalPage = (int)ceil($total / $pageSize);
                            foreach (range(1, $totalPage) as $page) {
                                $where['page'] = $page;
                                dispatch(new ItemBatchDownloadJob($where));
                                /*try {
                                    Adaptor::platform('jingdong')->download(AdaptorTypeEnum::ITEM_BATCH, $where);
                                } catch (\Exception $e) {
                                    dispatch(new ItemBatchDownloadJob($where));
                                }*/
                            }
                        }
                        $configServer->setNextQueryAt($item['end']);
                    }
                }
            } catch (\Exception $e) {
                Log::debug(__CLASS__ . $e->getMessage());
            } finally {
                $lock->release();
            }
        }

        return $result;
    }
}
