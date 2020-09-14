<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdPushQueue
 * @package App\Models
 * @version February 24, 2020, 1:59 pm CST
 *
 * @property integer id
 * @property string bis_id
 * @property unsignedBigInteger platform
 * @property string hub
 * @property string method
 * @property unsignedTinyInteger status
 * @property json extends
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdPushQueue extends Model
{
    use PlatformTrait;
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];
    protected $table = 'sys_std_push_queue';

    // 追加属性
    protected $appends = ['force_push'];

    protected $fillable = [
        'id',
        'bis_id',
        'platform',
        'hub',
        'method',
        'status',
        'extends',
        'try_times',
        'retry_after',
        'push_version',
        'push_content',
        'created_at',
        'updated_at',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'          => 'integer',
        'bis_id'      => 'string',
        'hub'         => 'string',
        'method'      => 'string',
        'try_times'   => 'string',
        'retry_after' => 'string',
        'push_version' => 'string',
        'push_content' => 'string',
        'extends'     => 'array',
    ];

    /**
     * 是否强制推送
     *
     * @return int
     *
     * @author linqihai
     * @since 2020/3/11 18:09
     */
    public function getForcePushAttribute()
    {
        $extends = $this->extends;
        $force = !empty($extends) && is_array($extends) && isset($extends['force_push']) && 1 == $extends['force_push'];

        return $force ? 1 : 0;
    }
}