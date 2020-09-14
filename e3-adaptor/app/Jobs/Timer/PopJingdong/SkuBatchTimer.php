<?php


namespace App\Jobs\Timer\PopJingdong;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\SkuTotal;
use App\Services\Adaptor\Jingdong\Jobs\SkuDownloadJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

class SkuBatchTimer extends CronJob
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
        $name = 'jingdong_sku_sync_jobs';

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
            $configServer = new ShopDownloadConfigServer($name, $shop['code']);
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

            $where = [
                'page'           => 1,
                'page_size'      => $pageSize,
                'start_created' => Carbon::createFromTimestamp($start)->toDateTimeString(),
                'end_created'   => Carbon::createFromTimestamp($end)->toDateTimeString(),
                'shop_code'   => $shop['code'],
            ];

            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 3 * 60);
            try {
                if ($lock->acquire()) {
                    // 查询总数，分页在job中查询下载
                    $skuServer = new SkuTotal($shop);
                    $total = $skuServer->count($where);
                    if ($total) {
                        $totalPage = ceil($total / $pageSize);
                        foreach (range(1, $totalPage) as $page) {
                            $where['page'] = $page;
                            try {
                                Adaptor::platform('jingdong')->download(AdaptorTypeEnum::SKU, $where);
                            } catch (\Exception $e) {
                                dispatch(new SkuDownloadJob($where));
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

        return $result;
    }
}
