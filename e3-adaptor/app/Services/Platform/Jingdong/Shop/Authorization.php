<?php


namespace App\Services\Platform\Jingdong\Shop;


use App\Models\Sys\Shop;
use App\Services\Platform\Contracts\Shop\AuthorizationContracts;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Http\Request;

class Authorization implements AuthorizationContracts
{
    public function call($shopId)
    {
        $shop = Shop::find($shopId);
        $appKey = $shop['app_key'];
        $callbackUrl = config('app.url') . '/shop_token/callback/jingdong';
        $state = base64_encode($callbackUrl) . '|' . $shopId;
        $state = base64_encode($state);
        $redirect_uri = config('app.url') . '/shop_token/callback/jingdong';
        $url = "https://open-oauth.jd.com/oauth2/to_login?app_key={$appKey}&response_type=code";
        $url .= "&redirect_uri=" . urlencode($redirect_uri) . "&state={$state}&scope=snsapi_base";

        return $url;
    }

    public function callback(Request $request)
    {
        $code = $request->get('code');
        $shopId = $request->get('state');
        $shop = Shop::find($shopId);
        $params = [];
        $params['grant_type'] = 'authorization_code';
        $params['app_key'] = $shop['app_key'];
        $params['app_secret'] = $shop['app_secret'];
        $params['grant_type'] = 'authorization_code';
        $params['code'] = $code;
        $data = $this->requestPlatform($params, 'access_token');
        // 更新token
        $shop['access_token'] = $data['access_token'];
        $shop['refresh_token'] = $data['refresh_token'];
        $shop['token_expired_at'] = $data['expires_in'];
        $shop->save();

        return $data;
    }

    public function refresh($shopId)
    {
        $shop = Shop::find($shopId);
        $params = [];
        $params['grant_type'] = 'refresh_token';
        $params['app_key'] = $shop['app_key'];
        $params['app_secret'] = $shop['app_secret'];
        $params['refresh_token'] = $shop['refresh_token'];
        $data = $this->requestPlatform($params, 'refresh_token');

        $shop['access_token'] = $data['access_token'];
        $shop['refresh_token'] = $data['refresh_token'];
        $shop['token_expired_at'] = $data['expires_in'];
        $shop->save();

        return $data;
    }

    private function requestPlatform($params, $method)
    {
        $data = array();
        $url = "https://open-oauth.jd.com/oauth2/{$method}";
        $uri = new Uri($url);
        $uri->withQuery(http_build_query($params));

        $options = array(
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'headers' => ['content-type' => 'application/json']
        );

        $client = new Client();
        $response = $client->post($uri, $options);
        $data = json_decode($response->getBody()->getContents(), true);
        if (isset($data['code']) && !empty($data['code'])) {
            \Log::error('get access token fail', $data);
            throw new \Exception("shop token request fail! ");
        }

        return $data;
    }
}