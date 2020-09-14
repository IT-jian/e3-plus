<?php


namespace App\Services\Platform\Taobao\Client\Top;


use App\Models\Sys\Shop;
use App\Services\Platform\Contracts\ClientManagerContract;
use App\Services\Platform\Taobao\Client\Top\Exceptions\TaobaoTopClientSideException;

class TopClientManager implements ClientManagerContract
{
    protected $clients = array();

    /**
     * 实例化指定平台服务
     *
     * @param $code
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/25 9:56
     */
    public function shop($code)
    {
        if (is_array($code)) {
            $code = $code['code'];
        }
        return $this->topClient($code);
    }

    public function makeClient($code)
    {
        $shop = Shop::where('code', $code)->first();
        if (empty($shop) || empty($shop['app_key']) || empty($shop['app_secret'])){
            throw new TaobaoTopClientSideException("Shop Code [{$code}] is not available.");
        }

        return app()->make(TopClientNew::class)->shop($shop);
    }

    public function forget($code)
    {
        unset($this->clients[$code]);
    }

    public function topClient($code)
    {
        if (!isset($this->clients[$code]))
        {
            $this->clients[$code] = $this->makeClient($code);
        }

        return $this->clients[$code];
    }
}
