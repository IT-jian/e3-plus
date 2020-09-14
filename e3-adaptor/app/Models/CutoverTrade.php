<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Support\Facades\Redis;


/**
 * 系统切换时，加强版报文订单
 *
 * Class CutoverTrade
 * @package App\Models
 */
class CutoverTrade extends Model
{
    protected $table = 'cutover_trade';

    protected $primaryKey = 'tid';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tid',
    ];

    public static function cacheAll()
    {
        self::chunk(100, function ($data) {
            $tids = $data->pluck('tid')->toArray();
            Redis::sadd('cutover_tids', $tids);
        });
    }

    public static function isCutover($tid)
    {
        Redis::sismembers('cutover_tids', $tid);
    }

    // @todo 移除
    public static function removeCache($tid)
    {
        Redis::delete('cutover_tids', $tid);
    }
}
