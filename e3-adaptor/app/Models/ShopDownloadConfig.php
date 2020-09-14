<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Carbon\Carbon;

/**
 * Class ShopDownloadConfig
 * @package App\Models
 * @version May 3, 2020, 8:18 pm CST
 *
 * @property integer id
 * @property string platform
 * @property string shop_code
 * @property string code
 * @property string name
 * @property unsignedTinyInteger stop_download
 * @property integer query_page_size
 * @property integer job_page_size
 * @property integer next_query_at
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class ShopDownloadConfig extends Model
{
    protected $table = 'shop_download_config';

    protected $fillable = [
        'id',
        'platform',
        'shop_code',
        'code',
        'name',
        'type',
        'stop_download',
        'query_page_size',
        'job_page_size',
        'next_query_at',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['next_query_at_cache'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'platform' => 'string',
        'shop_code' => 'string',
        'type' => 'string',
        'stop_download' => 'string',
        'code' => 'string',
        'name' => 'string',
        'query_page_size' => 'integer',
        'job_page_size' => 'integer',
        'next_query_at'   => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function getNextQueryAtCacheAttribute()
    {
        $timestamp = (new ShopDownloadConfigServer($this->code, $this->shop_code))->getNextQueryAt(strtotime($this->next_query_at));

        return Carbon::createFromTimestamp($timestamp)->toDateTimeString();
    }
}
