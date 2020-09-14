<?php


namespace App\Services;


use App\Models\ShopDownloadConfig;
use Cache;
use Carbon\Carbon;

class ShopDownloadConfigServer
{
    /**
     * 任务跑了 10 次后，同步时间到数据库
     */
    CONST NEXT_QUERY_AT_SYNC_AFTER_TIMES = 10;
    /**
     * @var ShopDownloadConfig|\Illuminate\Database\Eloquent\Model|object|null
     */
    private $config;

    public function __construct($code, $shopCode, $init = 0)
    {
        $this->initConfig($code, $shopCode, $init);
    }

    public function initConfig($code, $shopCode, $init = 0)
    {
        $config = Cache::remember($this->getConfigCacheKey($code, $shopCode), 12 * 60 * 60, function () use ($code, $shopCode) {
            $config = ShopDownloadConfig::where('code', $code)->where('shop_code', $shopCode)->first();
            if (empty($config)) {
                $config = [];
            }

            return $config;
        });
        if ($init) {
            $config = ShopDownloadConfig::where('code', $code)->where('shop_code', $shopCode)->first();
        }
        if (empty($config)) {
            throw new \Exception('download config not exists');
        }
        $this->config = $config;
    }

    public function getConfigCacheKey($code = '', $shopCode = '')
    {
        if ($code) {
            return 'download_config:' . $shopCode . ':' . $code . '_cache';
        }

        return 'download_config:' . $this->config->shop_code . ':' .$this->config->code . '_cache';
    }

    /**
     * 下载配置
     *
     * @return ShopDownloadConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * 获取下次查询时间
     * 缓存 --> DB --> default
     *
     * @param $default
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/20 11:01
     */
    public function getNextQueryAt($default)
    {
        $nextQueryAt = Cache::get($this->getNextQueryAtCacheKey());
        if (empty($nextQueryAt)) {
            if (!empty($this->config->next_query_at)) {
                $nextQueryAt = strtotime($this->config->next_query_at);
            } else {
                $nextQueryAt = $default;
            }
        }

        return $nextQueryAt;
    }

    public function getNextQueryAtCacheKey()
    {
        return 'download_config:' . $this->config->shop_code . ':' .$this->config->code . '_next_at';
    }

    /**
     * 获取锁之后，再设置下次开始时间
     *
     * @param $timestamp
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setNextQueryAtLock($timestamp)
    {
        if ($timestamp) {
            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($this->getConfigLockCacheKey(), 5);
            try {
                if ($lock->acquire()) {
                    $this->setNextQueryAt($timestamp);
                } else {
                    throw new \Exception('get redis lock failed, try later');
                }
            } catch (\Exception $e) {
                return ['status' => false, 'message' => $e->getMessage()];
            } finally {
                $lock->release();
            }
        }

        return ['status' => true];
    }

    public function getConfigLockCacheKey()
    {
        return 'download_config:' . $this->config->shop_code . ':' .$this->config->code . '_lock_at';
    }

    /**
     * 更新下次查询时间
     *
     * @param $timestamp
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setNextQueryAt($timestamp)
    {
        if ($timestamp) {
            Cache::forever($this->getNextQueryAtCacheKey(), $timestamp);
            // 任务执行 10 次，自动保存一次到数据库
            $this->syncDbCounter($timestamp);
        }
    }

    /**
     * 同步DB计数器
     *
     * @param $timestamp
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function syncDbCounter($timestamp)
    {
        $key = 'download_config:' . $this->config->shop_code . ':' .$this->config->code . '_sync_counter';
        $times = Cache::get($key, 1);
        if ($times >= self::NEXT_QUERY_AT_SYNC_AFTER_TIMES) {
            $this->config->next_query_at = Carbon::createFromTimestamp($timestamp);
            ShopDownloadConfig::where('code', $this->config->code)
                ->where('shop_code', $this->config->shop_code)
                ->update(['next_query_at' => Carbon::createFromTimestamp($timestamp)]);
            // 重新计数
            Cache::set($key, 1);
            // Log::debug('正在同步最后获取时间', [Carbon::createFromTimestamp($timestamp)]);
        } else {
            Cache::increment($key);
        }
    }

    public function removeConfigCache()
    {
        $code = $this->config->code;
        $shopCode = $this->config->shop_code;
        Cache::forget($this->getConfigCacheKey($code, $shopCode));
    }
}
