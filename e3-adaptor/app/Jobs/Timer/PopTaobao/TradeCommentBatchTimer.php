<?php


namespace App\Jobs\Timer\PopTaobao;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Taobao\Api\TradeRates;
use App\Services\Adaptor\Taobao\Jobs\TradeCommentDownloadJob;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Exception;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Illuminate\Support\Carbon;
use Log;

class TradeCommentBatchTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 30;// 每 30 s运行一次
    }

    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'taobao_comment_sync_jobs';

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
            Log::error('not available shop seller nick for trade comment batch timer');

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
            $pageSize = isset($config['query_page_size']) && $config['query_page_size'] > 0 ? $config['query_page_size'] : 100;
            // 任务批量大小
            $jobBatch = isset($config['job_page_size']) && $config['job_page_size'] > 0 ? $config['job_page_size'] : 100;
            // 开始时间
            $start = $configServer->getNextQueryAt(strtotime('-40 seconds'));
            $end = strtotime('-10 seconds');
            if ($start >= $end) {
                $start = strtotime('-45 seconds');
            }

            $page = 1;
            $where = [
                'page'           => 1,
                'page_size'      => $pageSize,
                'start_modified' => Carbon::createFromTimestamp($start)->toDateTimeString(),
                'end_modified'   => Carbon::createFromTimestamp($end)->toDateTimeString(),
            ];

            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 10 * 60);
            try {
                if ($lock->acquire()) {
                    $ratesServer = (new TradeRates($shop));
                    do {
                        $where['page'] = $page;
                        // 查询列表
                        $response = $ratesServer->page($where);
                        if (empty($response)) {
                            break;
                        }
                        if ($rates = data_get($response, 'trade_rates.trade_rate', [])) {
                            try { // 直接下载
                                Adaptor::platform('taobao')->download(AdaptorTypeEnum::COMMENTS, $rates);
                            } catch (Exception $exception) {
                                dispatch(new TradeCommentDownloadJob($rates));
                            }
                        }
                        if ($hasNext = data_get($response, 'has_next', false)) {
                            $page++;
                        } else {
                            break;
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
