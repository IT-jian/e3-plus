<?php


namespace App\Services\Platform\Jingdong\Client\Jos;


use App\Models\Sys\Shop;
use App\Services\Platform\Contracts\ClientManagerContract;
use App\Services\Platform\Exceptions\PlatformClientSideException;

class JosClientManager implements ClientManagerContract
{
    protected $clients = array();

    /**
     * 实例化指定平台服务
     *
     * @param $code
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/20 15:31
     */
    public function shop($code)
    {
        return $this->josClient($code);
    }

    public function josClient($code)
    {
        if (is_array($code)) {
            $code = $code['code'];
        }
        if (!isset($this->clients[$code])) {
            $this->clients[$code] = $this->makeClient($code);
        }

        return $this->clients[$code];
    }

    public function makeClient($code)
    {
        $shop = Shop::getShopByCode($code);
        if (empty($shop) || empty($shop['app_key']) || empty($shop['app_secret'])) {
            throw new PlatformClientSideException("Shop Code [{$code}] is not available.");
        }

        return app()->make(JosClient::class)->shop($shop);
    }

    public function forget($code)
    {
        unset($this->clients[$code]);
    }
}
