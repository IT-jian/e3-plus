<?php


namespace App\Jobs\Timer\PopJingdong;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Api\StepTradeCount;
use App\Services\Adaptor\Jingdong\Api\StepTradePage;
use App\Services\Adaptor\Jingdong\Jobs\JingdongStepTradeBatchTransferJob;
use App\Services\Adaptor\Jingdong\Jobs\StepTradeDownloadJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Log;

class StepTradeBatchTimer extends CronJob
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
        $name = 'jingdong_step_trade_sync_jobs';

        // 平台级别下载设置
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
            Log::info('not available shop seller nick for step trade timer');

            return true;
        }
        $orderStatusItem = [1, 2, 3];
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
            $pageSize = isset($config['query_page_size']) && $config['query_page_size'] > 0 ? $config['query_page_size'] : 100;
            // 任务批量大小
            $jobBatch = isset($config['job_page_size']) && $config['job_page_size'] > 0 ? $config['job_page_size'] : 5;
            // 开始时间
            $start = $configServer->getNextQueryAt(strtotime('-20 seconds'));
            $end = strtotime('-10 seconds');
            if ($start >= $end) {
                $start = strtotime('-20 seconds');
            }

            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 10 * 60);
            try {
                if ($lock->acquire()) {
                    $countWhere = [
                        'start_modified' => Carbon::createFromTimestamp($start)->toDateTimeString(),
                        'end_modified'   => Carbon::createFromTimestamp($end)->toDateTimeString(),
                    ];
                    $countApi = new StepTradeCount($shop);
                    $pageApi = new StepTradePage($shop);
                    foreach ($orderStatusItem as $status) {
                        $countWhere['status'] = $status;
                        $total = $countApi->count($countWhere);
                        if ($total > 0) {
                            $pageTotal = ceil($total / $pageSize);
                            $where = [
                                'page'           => 1,
                                'page_size'      => $pageSize,
                                'start_modified' => Carbon::createFromTimestamp($start)->toDateTimeString(),
                                'end_modified'   => Carbon::createFromTimestamp($end)->toDateTimeString(),
                            ];

                            for ($page = 1; $page <= $pageTotal; $page++) {
                                $where['page'] = $page;
                                $trades = $pageApi->page($where);
                                if (empty($trades)) {
                                    continue;
                                }
                                $orderIds = array_column($trades, 'orderId');
                                // 分发任务
                                dispatch((new StepTradeDownloadJob($trades))->chain(
                                    [
                                        new JingdongStepTradeBatchTransferJob(['order_ids' => $orderIds]),
                                    ]));
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
