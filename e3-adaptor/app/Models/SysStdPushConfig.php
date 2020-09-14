<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Cache;

/**
 * Class SysStdPushConfig
 * @package App\Models
 * @version March 2, 2020, 4:56 pm CST
 *
 * @property integer id
 * @property string method
 * @property unsignedTinyInteger stop_push
 * @property unsignedTinyInteger request_once
 * @property unsignedInteger try_times
 * @property unsignedInteger tries
 * @property unsignedInteger retry_after
 * @property unsignedInteger delay
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdPushConfig extends Model
{
    const MAP_CACHE = 'cache_std_sys_config_map';
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'method'       => 'required',
        'stop_push'    => 'required',
        'request_once' => 'required',
    ];
    protected $table = 'sys_std_push_config';
    protected $fillable = [
        'id',
        'method',
        'proxy',
        'on_queue',
        'stop_push',
        'push_sort',
        'request_once',
        'try_times',
        'tries',
        'retry_after',
        'delay',
        'created_at',
        'updated_at',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'           => 'integer',
        'method'       => 'string',
        'push_sort'    => 'string',
        'stop_push'    => 'string',
        'proxy'    => 'string',
        'request_once' => 'string',
    ];

    /**
     *
     * @author linqihai
     * @since 2020/2/24 20:36
     */
    public static function clearMapCache()
    {
        Cache::forget(self::MAP_CACHE);
    }

    /**
     *
     * @param string $method
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/2 17:20
     */
    public function methodMapCache($method = '')
    {
        $map = Cache::remember(self::MAP_CACHE, 60 * 60, function () {
            $map = static::get()->keyBy('method');

            return $map;
        });

        return !empty($method) && isset($map[$method]) ? $map[$method] : $map;
    }
}
