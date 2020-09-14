<?php


namespace App\Jobs\Timer\PopTaobao;


use App\Models\Sys\Shop;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Taobao\Api\Exchange;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\ExchangeBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\TradeBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Exception;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Illuminate\Support\Carbon;
use Log;

class ExchangeBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 30;// 每 10 秒运行一次
    }

    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'taobao_exchange_sync_jobs';

        $platformConfigServer = new PlatformDownloadConfigServer($name);
        $config = $platformConfigServer->getConfig();
        if (isset($config) && 1 == $config['stop_download']) { // 停止下载，则不再处理
            return true;
        }

        // 查询系统商店单据
        $sellerNicks = [];
        $shops = Shop::available('taobao')->get()->toArray();
        foreach ($shops as $shop) {
            $sellerNicks[] = $shop['seller_nick'];
        }

        if (empty($sellerNicks)) {
            Log::info('not available shop seller nick for exchange timer');

            return true;
        }

        $result = true;
        $end = strtotime('-10 seconds');

        foreach ($shops as $shop) {
            // 店铺级别下载设置
            try {
                $configServer = new ShopDownloadConfigServer($name, $shop['code']);
            } catch (Exception $e) {
                continue;
            }
            $config = $configServer->getConfig();
            if (isset($config) && 1 == $config['stop_download']) { // 停止下载，则不再处理
                continue;
            }
            // 锁名
            $lockName = $configServer->getConfigLockCacheKey();
            // 查询页大小
            $pageSize = isset($config['query_page_size']) && $config['query_page_size'] > 0 ? $config['query_page_size'] : 30;
            // 任务批量大小
            $jobBatch = isset($config['job_page_size']) && $config['job_page_size'] > 0 ? $config['job_page_size'] : 30;
            // 开始时间
            $start = $configServer->getNextQueryAt(strtotime('-40 seconds'));
            $end = strtotime('-10 seconds');
            if ($start >= $end) {
                $start = strtotime('-40 seconds');
            }
            $endTemp = 0;
            $timeRange = [];
            // 如果超过了5分钟，则划分每5分钟下载一次
            do {
                $endTemp = $start + 300;
                if ($endTemp > $end) {
                    $endTemp = $end+1;
                }
                $timeRange[] = [
                    'start' => $start - 2, // 前后覆盖2秒
                    'end' => $endTemp,
                ];
                $start = $endTemp;
            } while ($end > $start);

            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 10 * 60);
            try {
                if ($lock->acquire()) {
                    $exchangeServer = (new Exchange($shop));
                    foreach ($timeRange as $item) {
                        $page = 1;
                        $where = [
                            'page'           => 1,
                            'page_size'      => $pageSize,
                            'start_modified' => Carbon::createFromTimestamp($item['start'])->toDateTimeString(),
                            'end_modified'   => Carbon::createFromTimestamp($item['end'])->toDateTimeString(),
                        ];
                        do {
                            $where['page'] = $page;
                            $exchanges = $exchangeServer->page($where);
                            if (empty($exchanges)) {
                                break;
                            }
                            $disputeIds = $tids = [];
                            foreach ((array)$exchanges as $exchange) {
                                $disputeIds[] = $exchange['dispute_id'];
                                $tids[] = $exchange['alipay_no'];
                            }

                            Log::debug($shop['code']. ' exchange batch download timer found:' . count($disputeIds));
                            // 分发任务
                            $params = ['dispute_ids' => $disputeIds, 'shop_code' => $shop['code'], 'key' => $page];
                            if (config('hubclient.cutover_date', '') && !empty($tids)) {// 系统上线期间，先下载转入对应的订单，再下载和转入换货单
                                $downloadTids = [];
                                // 查询是否有未下载的订单
                                $existTids = SysStdTrade::whereIn('tid', $tids)->get(['tid']);
                                if ($existTids->isEmpty()) {
                                    $downloadTids = $tids;
                                } else {
                                    $downloadTids = array_diff($tids, $existTids->pluck('tid')->toArray());
                                }
                                if ($downloadTids) {
                                    $key = "exchange-trade-page-$page";
                                    dispatch((new TradeBatchDownloadJob(['tids' => $tids, 'platform' => 'taobao', 'key' => $key]))->chain(
                                        [
                                            new TaobaoTradeBatchTransferJob(['tids' => $tids, 'key' => $key]),
                                            new ExchangeBatchDownloadJob($params)
                                        ]));
                                } else {
                                    dispatch(new ExchangeBatchDownloadJob($params));
                                }
                            } else {
                                dispatch(new ExchangeBatchDownloadJob($params));
                            }
                            $page++;
                        } while (true);
                        $configServer->setNextQueryAt($item['end']);
                    }
                }
            } catch (\Exception $e) {
                Log::debug($shop['code'] . __CLASS__ . $e->getMessage());
            } finally {
                $lock->release();
            }
        }

        $platformConfigServer->setNextQueryAt($end);
        return $result;
    }
}
