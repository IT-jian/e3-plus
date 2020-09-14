<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sys\Shop;
use App\Services\Platform\ShopAuthorizationManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 店铺 token 管理
 * Class ShopTokenController
 * @package App\Http\Controllers\Admin
 *
 * @author linqihai
 * @since 2019/12/15 15:49
 */
class ShopTokenController extends Controller
{
    /** 获取请求 token 的 url
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/15 15:47
     */
    public function callToken($id)
    {
        $shop = Shop::find($id);
        $callUrl = (new ShopAuthorizationManager())->platform($shop['platform'])->call($id);

        return $this->respond($callUrl);
    }

    /**
     * 刷新 token 地址
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/15 15:47
     */
    public function refreshToken($id)
    {
        $shop = Shop::find($id);
        $callUrl = (new ShopAuthorizationManager())->platform($shop['platform'])->refresh($id);

        return $this->respond($callUrl);
    }

    /**
     * token回调地址
     *
     * @param $platform
     * @param Request $request
     *
     * @author linqihai
     * @since 2019/12/15 15:48
     */
    public function callbackToken($platform, Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
            'state' => 'required'
        ]);
        $token = (new ShopAuthorizationManager())->platform($platform)->callback($request);

        return $this->success([]);
    }
}
