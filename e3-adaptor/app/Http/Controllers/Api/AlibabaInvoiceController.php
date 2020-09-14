<?php


namespace App\Http\Controllers\Api;


use App\Facades\HubApi;
use App\Http\Controllers\Controller;
use App\Models\SysStdTrade;
use App\Services\AisinoInvoiceServer;
use App\Services\Platform\Exceptions\PlatformServerSideException;
use Illuminate\Http\Request;

/**
 * 阿里发票
 *
 * Class AlibabaInvoiceController
 * @package App\Http\Controllers\Api
 */
class AlibabaInvoiceController extends Controller
{
    /**
     * 回写发票详情
     *
     * @param Request $request
     * @return array|mixed
     */
    public function store(Request $request)
    {
        $token = config('hubclients.asino.sign_pwd', '');
        if ($request->header('token', '1') != $token) {

            return $this->failed('Invalid Header Token');
        }

        $input = $request->input();
        $sysStdTrade = SysStdTrade::platform('taobao')->where('tid', $input['platform_tid'])->firstOrFail();
        $input['shop_code'] = $sysStdTrade['shop_code'];
        $content = $input;

        try {
            $result = (new AisinoInvoiceServer())->updateDetailApi($content);
        } catch (\Exception $e) {
            // 平台服务端异常请求
            if ($e instanceof PlatformServerSideException) {
                $this->setSubData($e->getResponseBody());
            }
            if ($e instanceof \RuntimeException) {
                return $this->failed($e->getMessage());
            }
            return $this->failed('adaptor-error: request fail！');
        }

        if (1 == $result['status']) {
            // 更新 adaptor 发票上传时间
            return $this->success($result['data']);
        }
        return $this->failed($result['message']);
    }
}
