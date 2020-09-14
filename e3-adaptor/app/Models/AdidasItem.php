<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class AdidasItem
 * @package App\Models
 * @version March 25, 2020, 3:30 pm CST
 *
 * @property integer id
 * @property string outer_sku_id
 * @property string item_id
 * @property string size
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class AdidasItem extends Model
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];
    protected $table = 'adidas_items';
    protected $fillable = [
        'id',
        'outer_sku_id',
        'item_id',
        'size',
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
        'outer_sku_id' => 'string',
        'item_id'      => 'string',
        'size'         => 'string',
    ];

    /**
     * map ID
     * @param $outerSkuId
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/25 15:44
     */
    public function mapItemId($outerSkuId)
    {
        $ttl = 12 * 60 * 60; // 过期时间 12 个小时
        $ttl += rand(0, 60) * 60; // 分散在一个小时内

        // 包含下划线，直接返回，不做mapping
        if (Str::contains($outerSkuId, ['_'])) {
            return $outerSkuId;
        }
        return Cache::remember($this->itemCacheKey($outerSkuId), $ttl, function () use ($outerSkuId) {
            $item = static::where('outer_sku_id', $outerSkuId)->first(['item_id', 'size']);
            if (empty($item)) {
                return $outerSkuId; // 不存在，则取 outer_sku_id
            }

            return $item['item_id'] . '_' . $item['size'];
        });
    }

    public function itemCacheKey($outerSkuId)
    {
        return "adidas_item:" . $outerSkuId;
    }

    /**
     * 清除缓存
     *
     * @param $outerSkuId
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @author linqihai
     * @since 2020/3/25 16:23
     */
    public function removeItemCache($outerSkuId)
    {
        Cache::delete($this->itemCacheKey($outerSkuId));
    }
}
