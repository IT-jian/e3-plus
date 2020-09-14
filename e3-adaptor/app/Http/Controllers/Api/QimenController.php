<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\Platform\Taobao\Qimen\QimenApi;
use Illuminate\Http\Request;

/**
 * 奇门请求入口
 *
 * Class QimenController
 * @package App\Http\Controllers\Api
 *
 * @author linqihai
 * @since 2020/3/23 13:59
 */
class QimenController extends Controller
{
    public function index(Request $request)
    {
        return (new QimenApi())->execute($request);
    }
}