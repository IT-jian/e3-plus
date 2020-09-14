<?php


namespace App\Services\Platform\Taobao\Shop;


use App\Facades\TopClient;
use App\Models\Sys\Shop;
use App\Services\Platform\Taobao\Client\Top\Request\TopAuthTokenCreateRequest;
use App\Services\Platform\Taobao\Client\Top\Request\TopAuthTokenRefreshRequest;
use Illuminate\Http\Request;
use App\Services\Platform\Contracts\Shop\AuthorizationContracts;

class Authorization implements AuthorizationContracts
{
    public function call($shopId)
    {
        $shop = Shop::findOrFail($shopId);
        $clientId = $shop['app_key'];
        $callbackUrl = config('app.url') . '/shop_token/callback/taobao';
        $state = base64_encode($callbackUrl) . '|' . $shopId;
        $state = base64_encode($state);
        $redirect_uri = config('app.url') . '/shop_token/callback/taobao';
        $url = "https://oauth.taobao.com/authorize?client_id={$clientId}&response_type=code";
        $url .= "&redirect_uri=" . urlencode($redirect_uri) . "&state={$state}&view=web";
        \Log::info('shop call url:' . $url);
        return $url;
    }

    /**
     * 淘宝回调入口
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     *
     * @author linqihai
     * @since 2019/12/31 17:56
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
        $shopId = $request->get('state');
        $shop = Shop::findOrFail($shopId);

        $request = (new TopAuthTokenCreateRequest());
        $request->setCode($code);
        $data = TopClient::shop($shop['code'])->shop($shop->toArray())->execute($request, $shop['access_token']);

        if (!isset($data['top_auth_token_create_response']['token_result']) || empty($data['top_auth_token_create_response']['token_result'])) {
            throw new \Exception('code 获取 token 失败，数据为空');
        }
        $token = json_decode($data['top_auth_token_create_response']['token_result'], true);
        // 更新token
        $shop['access_token'] = $token['access_token'];
        $shop['refresh_token'] = $token['refresh_token'];
        $shop['token_expired_at'] = $token['expire_time'];
        $shop->save();

        return $token;
    }

    /**
     * 淘宝刷新入口
     *
     * @param $shopId
     * @return mixed
     * @throws \Exception
     *
     * @author linqihai
     * @since 2019/12/31 17:57
     */
    public function refresh($shopId)
    {
        $shop = Shop::findOrFail($shopId);

        $request = (new TopAuthTokenRefreshRequest());
        $request->setRefreshToken($shop['refresh_token']);
        $data = TopClient::shop($shop['code'])->shop($shop->toArray())->execute($request, $shop['access_token']);
        if (!isset($data['top_auth_token_refresh_response']['token_result']) || empty($data['top_auth_token_refresh_response']['token_result'])) {
            throw new \Exception('获取失败，数据为空');
        }
        $token = json_decode($data['top_auth_token_refresh_response']['token_result'], true);

        $shop['access_token'] = $token['access_token'];
        $shop['refresh_token'] = $token['refresh_token'];
        $shop['token_expired_at'] = $token['expire_time'];
        $shop->save();

        return $token;
    }
}