<?php


namespace App\Services;


use App\Models\PlatformDownloadConfig;
use Cache;
use Carbon\Carbon;

class PlatformDownloadConfigServer
{
    /**
     * 任务跑了 10 次后，同步时间到数据库
     */
    CONST NEXT_QUERY_AT_SYNC_AFTER_TIMES = 10;
    /**
     * @var PlatformDownloadConfig|\Illuminate\Database\Eloquent\Model|object|null
     */
    private $config;

    public function __construct($code)
    {
        $this->initConfig($code);
    }

    public function initConfig($code)
    {
        $config = Cache::remember($this->getConfigCacheKey($code), 12 * 60 * 60, function () use ($code) {
            $config = PlatformDownloadConfig::where('code', $code)->first();
            if (empty($config)) {
                $config = [];
            }

            return $config;
        });
        if (empty($config)) {
            throw new \Exception('download config not exists');
        }
        $this->config = $config;
    }

    public function getConfigCacheKey($code = '')
    {
        if ($code) {
            return $code . '_cache';
        }

        return $this->config->code . '_cache';
    }

    /**
     * 下载配置
     *
     * @return PlatformDownloadConfig
     *
     * @author linqihai
     * @since 2020/3/20 11:01
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
        return $this->config->code . '_next_at';
    }

    /**
     * 获取锁之后，再设置下次开始时间
     *
     * @param $timestamp
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @author linqihai
     * @since 2020/3/20 11:16
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
        return $this->config->code . '_lock_at';
    }

    /**
     * 更新下次查询时间
     *
     * @param $timestamp
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @author linqihai
     * @since 2020/3/20 11:01
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
     *
     * @author linqihai
     * @since 2020/3/20 10:29
     */
    public function syncDbCounter($timestamp)
    {
        $key = $this->config->code . '_sync_counter';
        $times = Cache::get($key, 1);
        if ($times >= self::NEXT_QUERY_AT_SYNC_AFTER_TIMES) {
            $this->config->next_query_at = Carbon::createFromTimestamp($timestamp);
            PlatformDownloadConfig::where('code', $this->config->code)->update(['next_query_at' => Carbon::createFromTimestamp($timestamp)]);
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
        Cache::forget($this->getConfigCacheKey($code));
        $this->initConfig($code);
    }
}