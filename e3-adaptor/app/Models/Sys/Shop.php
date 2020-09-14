<?php

namespace App\Models\Sys;

use App\Models\BaseModel as Model;
use App\Models\PlatformTrait;

class Shop extends Model
{
    use PlatformTrait;
    protected $table = 'shops';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name', 'platform', 'app_key', 'app_url', 'app_secret', 'seller_nick', 'access_token', 'refresh_token', 'token_expired_at', 'extends'
    ];

    protected $casts = [
        'token_expired_at' => 'datetime',
        'extends' => 'array',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        // 'app_secret', 'access_token', 'refresh_token'
    ];

    /**
     * 返回可用的平台店铺
     *
     * @param Model $query
     * @param string $platform
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/2 10:18
     */
    public function scopeAvailable($query, $platform)
    {
        return $query->where('platform', $platform)
            ->where('app_key', '!=', '')
            ->where('app_secret', '!=', '')
            ->where('seller_nick', '!=', '')
            ->where('access_token', '!=', '');
    }

    /**
     * 店铺 seller nick map
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/2 11:05
     */
    public static function getSellerNickShopCodeMap()
    {
        $map = \Cache::remember('shop_seller_nick_code_map', 600, function () {
            $shops = Shop::all(['seller_nick', 'code']);
            if ($shops->isEmpty()) {
                return [];
            }

            return $shops->pluck('code', 'seller_nick')->toArray();
        });

        return $map;
    }

    public static function getShopByCode($code)
    {
        $shops = self::cacheAllShop();
        foreach ($shops as $shop) {
            if ($code == $shop['code']) {
                return $shop;
            }
        }

        return [];
    }

    public static function getShopByNick($sellerNick)
    {
        $shops = self::cacheAllShop();
        foreach ($shops as $shop) {
            if ($sellerNick == $shop['seller_nick']) {
                return $shop;
            }
        }

        return [];
    }

    public static function cacheAllShop()
    {
        $shops = \Cache::remember('all_shops', 600, function () {
            $shops = Shop::all();
            if ($shops->isEmpty()) {
                return [];
            }

            return $shops->toArray();
        });

        return $shops;
    }

    public function clearCache()
    {
        \Cache::delete('shop_seller_nick_code_map');
        \Cache::delete('all_shops');
    }
}
