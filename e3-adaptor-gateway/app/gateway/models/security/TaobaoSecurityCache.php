<?php
/*
 * File name
 * 
 * @author xy.wu
 * @since 2020/3/30 16:02
 */

namespace gateway\app\gateway\models\security;

use gateway\boot\CommonTool;
use iTCache;

class TaobaoSecurityCache implements iTCache
{
    /**
     * 加解密secret，缓存在内存中
     *
     * @var
     */
    private static $secretContext = [];

    /**
     * Method getCache
     * 获取缓存的cache
     *
     * @param $key
     * @return mixed
     * @author xy.wu
     * @since 2020/3/30 16:11
     */
    public function getCache($key)
    {
        $cacheKey = md5($key);

        if (!isset(self::$secretContext[$cacheKey]) || empty(self::$secretContext[$cacheKey])) {
            $cache = CommonTool::loadCache();
            self::$secretContext[$cacheKey] = $cache->fetch($cacheKey);
        }

        return self::$secretContext[$key];
    }

    /**
     * Method setCache
     * 设置缓存cache
     *
     * @param $key
     * @param $value
     * @return bool
     * @author xy.wu
     * @since 2020/3/30 16:12
     */
    public function setCache($key, $value)
    {
        $cacheKey = md5($key);

        $cache = CommonTool::loadCache();
        $cache->save($cacheKey, $value, (8600));

        self::$secretContext[$cacheKey] = $value;

        return true;
    }
}
