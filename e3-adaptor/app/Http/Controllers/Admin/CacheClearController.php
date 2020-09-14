<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 清除缓存控制器
 *
 * Class AdidasItemController
 * @package App\Http\Controllers\Admin
 */
class CacheClearController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @author linqihai
     * @since 2020/3/25 16:33
     */
    public function index(Request $request)
    {
        $prefix = $request->input('cache_type');
        $key = $request->input('key', '*');
        // 需要在前面连接上应用的缓存前缀
        $keys = app('redis')->keys(config('cache.prefix') . ':' . $prefix . ':*');

        app('redis')->del($keys);

        return $this->setStatusCode(Response::HTTP_CREATED)->success([]);
    }
}
