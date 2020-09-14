<?php


namespace App\Services\Platform\Taobao\Qimen;


use App\Models\Sys\Shop;
use App\Services\Platform\Taobao\Qimen\Exceptions\BadMethodExcption;
use App\Services\Platform\Taobao\Qimen\Exceptions\InvalidSignException;
use App\Services\Platform\Taobao\Qimen\Exceptions\ServerSideException;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;

class QimenApi
{
    private $methodMap = [
        'qimen.taobao.qianniu.cloudkefu.address.self.modify'  => 'AddressSelfModify', //自助修改接口
        'qimen.taobao.qianniu.cloudkefu.order.self.intercept' => 'OrderSelfIntercept', //自助自助锁单接口
    ];

    /**
     * 执行请求
     * @param Request $request
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/23 13:17
     */
    public function execute(Request $request)
    {
        try {
            // 校验app_key
            $appSecret = $this->checkAppKey($request);
            // sign
            $this->checkSign($request, $appSecret);

            $qimenApi = $this->resolveApi($request->get('method', ''));
            $content = $this->getContent($request);
            $result = $qimenApi->execute($content);
            if (!$result['status']) {
                throw new ServerSideException($result['message']);
            }
            $result = [
                'success' => 'true',
            ];
        } catch (\Exception $exception) {
            $result = [
                'success'   => 'false',
                'errorCode' => $exception->getCode(),
                'errorMsg'  => $exception->getMessage(),
            ];
        }
        $format = $request->get('format', 'xml');

        return $this->parseResponse($result, $format);
    }

    /**
     * 校验key是否存在
     *
     * @param $request
     * @return string app_secret
     *
     * @author linqihai
     * @since 2020/3/23 12:04
     */
    public function checkAppKey(Request $request)
    {
        $appKey = $request->get('app_key');
        if ($appKey) {
            $shop = Shop::where('app_key', $appKey)->where('platform', 'taobao')->first();
            if (!is_null($shop) && $shop->app_key && $shop->app_secret) {
                return $shop->app_secret;
            }
        }

        throw new InvalidSignException('invalid app params');
    }

    /**
     * 校验签名
     *
     * @param Request $request
     * @param String $appSecret
     * @return String sign
     *
     * @author linqihai
     * @since 2020/3/23 12:04
     */
    public function checkSign(Request $request, $appSecret)
    {
        $sign = '';
        $queryParams = $request->query();
        $requestSign = $queryParams['sign'];
        unset($queryParams['sign']);
        foreach ($queryParams as $key => $value) {
            $sign .= $key . $value;
        }
        $sign .= $request->getContent();
        if (isset($queryParams['sign_method']) && strtolower($queryParams['sign_method']) == 'hmac') {
            $sign = strtoupper(hash_hmac('md5', $sign, $appSecret));
        } else {
            $sign = strtoupper(md5($appSecret . $sign . $appSecret));
        }
        if ($requestSign == $sign) {
            return $sign;
        } else {
            \Log::debug('correct sign：' . $sign);
        }
        $msg = 'sign-check-failure';
        if ('production' != app()->environment()) {
            $msg .= ": {$sign}";
        }
        throw new InvalidSignException($msg);
    }

    /**
     * 解析接口类
     *
     * @param $qimenMethod
     * @return ApiContracts
     *
     * @author linqihai
     * @since 2020/3/23 12:03
     */
    public function resolveApi($qimenMethod)
    {
        $method = $this->methodMap[$qimenMethod];
        $className = "App\Services\Platform\Taobao\Qimen\Api\\";
        $className .= ucfirst($method) . 'Api';
        if (!class_exists($className)) {
            throw new BadMethodExcption("Adaptor Has No Such Qimen Api：" . $qimenMethod);
        }

        return app()->make($className);
    }

    /**
     * 解析内容
     *
     * @param $request
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/23 12:04
     */
    public function getContent($request)
    {
        $content = json_decode($request->getContent(), true);

        return $content;
    }

    public function parseResponse($result, $format)
    {
        if ('xml' == $format) {
            $responseString = ArrayToXml::convert($result, 'result', false, 'UTF-8');
        } else {
            $responseString = json_encode(['result' => $result]);
        }

        return $responseString;
    }
}