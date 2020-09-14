<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdReasonMap
 * @package App\Models
 * @version February 24, 2020, 2:50 pm CST
 *
 * @property unsignedInteger id
 * @property string platform
 * @property string type
 * @property string source_name
 * @property string map_name
 * @property string remark
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdReasonMap extends Model
{
    protected $table = 'sys_std_reason_map';

    const MAP_CACHE = 'cache_std_sys_reason_map';

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'platform'    => 'required',
        'type'        => 'required',
        'source_name' => 'required|max:255',
        'map_name'    => 'max:255',
    ];
    protected $fillable = [
        'id',
        'platform',
        'type',
        'source_name',
        'map_name',
        'remark',
        'created_at',
        'updated_at',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'platform'    => 'string',
        'type'        => 'string',
        'source_name' => 'string',
        'map_name'    => 'string',
        'remark'      => 'string',
    ];

    /**
     *
     * @author linqihai
     * @since 2020/2/24 20:36
     */
    public static function clearMapCache()
    {
        \Cache::forget(self::MAP_CACHE);
    }

    /**
     * @param $platform
     * @param $type
     * @return mixed
     *
     * @author linqihai
     * @since 2020/2/24 20:29
     */
    public function reasonMapCache($platform, $type)
    {
        $map = \Cache::remember(self::MAP_CACHE, 60 * 60, function () use ($type) {
            $list = static::get()->toArray();
            $map = [];
            foreach ($list as $item) {
                $map[$item['platform']][$item['type']][$item['source_name']] = $item['map_name'];
            }

            return $map;
        });

        return isset($map[$platform]) ? (isset($map[$platform][$type]) ? $map[$platform][$type] : []) : [];
    }

}