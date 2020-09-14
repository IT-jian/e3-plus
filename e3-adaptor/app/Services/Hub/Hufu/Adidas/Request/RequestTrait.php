<?php


namespace App\Services\Hub\Hufu\Adidas\Request;


use App\Models\Sys\Shop;
use Carbon\Carbon;

trait RequestTrait
{
    public function setExtendQuery($data, $shopCode)
    {
        $config = config('hubclient.clients.hufu');
        $shop = Shop::getShopByCode($shopCode);

        $data['session'] = $shop['access_token'];
        $data = json_encode($data);
        $query = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'sign' => $this->getSign($config, $data),
            'app_key' => $shop['app_key'],
        ];
        $this->setQuery($query, true);
        $this->data = $data;
    }

    public function getSign($config, $data)
    {
        $clientId = $config['app_key'];
        $clientSecret = $config['app_secret'];
        $sign = md5($clientId . md5($clientId . serialize($data) . $clientSecret) . md5($clientId . $clientSecret));

        return $sign;
    }
}
