<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use App\Services\PlatformDownloadConfigServer;
use Carbon\Carbon;

/**
 * Class PlatformDownloadConfig
 * @package App\Models
 * @version March 19, 2020, 4:28 pm CST
 *
 * @property integer id
 * @property string platform
 * @property string code
 * @property string name
 * @property unsignedTinyInteger stop_download
 * @property integer query_page_size
 * @property integer job_page_size
 * @property integer next_query_at
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class PlatformDownloadConfig extends Model
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];
    protected $table = 'platform_download_config';

    protected $fillable = [
        'id',
        'platform',
        'code',
        'name',
        'type',
        'stop_download',
        'query_page_size',
        'job_page_size',
        'next_query_at',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['next_query_at_cache'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'              => 'integer',
        'platform'        => 'string',
        'code'            => 'string',
        'type'            => 'string',
        'name'            => 'string',
        'stop_download'   => 'string',
        'query_page_size' => 'integer',
        'job_page_size'   => 'integer',
        'next_query_at'   => 'datetime',
    ];

    public function getNextQueryAtCacheAttribute()
    {
        $timestamp = (new PlatformDownloadConfigServer($this->code))->getNextQueryAt(strtotime($this->next_query_at));

        return Carbon::createFromTimestamp($timestamp)->toDateTimeString();
    }
}
