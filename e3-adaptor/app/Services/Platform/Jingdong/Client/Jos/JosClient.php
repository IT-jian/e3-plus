<?php


namespace App\Services\Platform\Jingdong\Client\Jos;


use App\Models\Sys\Shop;
use App\Services\Platform\AbstractHttpApiClient;
use App\Services\Platform\Exceptions\PlatformAppCallLimitedException;
use App\Services\Platform\Exceptions\PlatformClientSideException;
use App\Services\Platform\Exceptions\PlatformServerSideException;
use App\Services\Platform\Exceptions\PlatformTokenInvalidException;
use App\Services\Platform\HttpClient\GuzzleAdapter;
use App\Services\Platform\Jingdong\Client\Jos\Exceptions\JosServerSideException;
use Illuminate\Support\Str;
use Laravel\Lumen\Application;

class JosClient extends AbstractHttpApiClient
{
    public $app;
    public $appKey;
    public $appSecret;
    public $accessToken;
    public $forceHttps = false;
    protected $httpGatewayUri = "http://api.jd.com/routerjson";
    protected $httpsGatewayUri = "https://eco.taobao.com/router/rest";
    protected $httpHostnameOverride = false;
    protected $httpsHostnameOverride = false;//不管$request如何规定，都使用https
    protected $signMethod = "md5";
    protected $sdkVersion = "jos-sdk-php";

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->app = $app;
        // 执行初始化事件
        $this->onInitialize();
    }

    public function onInitialize()
    {

    }

    public function shop($shop)
    {
        if (is_string($shop)) {
            $shop = Shop::where('code', $shop)->first();
        }
        $this->appKey = $shop['app_key'];
        $this->appSecret = $shop['app_secret'];
        $this->accessToken = $shop['access_token'];

        return $this;
    }

    /**
     * 支持使用本地映射的时候 使用 如下代码自定义网关API/HOST头 目前不设置host没影响 但是不代表以后没影响
     * $client->setGatewayUri('http://127.0.0.1:8899/router/rest#gw.api.taobao.com');
     * $client->setGatewayUri('https://127.0.0.1:7788/router/rest#eco.taobao.com');
     *
     * @param      $uri
     * @param null $secure
     *
     * @return $this
     */
    public function setGatewayUri(string $uri, $secure = null)
    {
        if (empty($uri)) {
            return $this;
        }
        if ($secure === null) {
            $uri = strtolower($uri);
            $secure = Str::startsWith($uri, 'https') ?: false;
        }
        if ($secure == false) {
            $gateWay = 'http';
        } elseif ($secure == true) {
            $gateWay = 'https';
        }
        $hashTag = strstr($uri, '#');
        if ($hashTag !== false) {
            $this->{$gateWay . 'HostnameOverride'} = str_replace('#', '', $hashTag);
            $uri = str_replace($hashTag, '', $uri);
        } else {
            $host = parse_url($this->{$gateWay . 'GatewayUri'});
            $this->{$gateWay . 'HostnameOverride'} = $host['host'];
        }
        $this->{$gateWay . 'GatewayUri'} = $uri;

        return $this;
    }

    public function getAppKey()
    {
        return $this->appKey;
    }

    public function execute($requests, $accessToken = null)
    {
        if (!$this->appKey || !$this->appSecret) {
            throw new PlatformClientSideException('App key and secret can not empty!');
        }

        $returnFirst = false;
        if (!is_array($requests)) {
            $returnFirst = true;
            $requests = [$requests];
        }
        if (null == $accessToken && !empty($this->accessToken)) {
            $accessToken = $this->accessToken;
        }
        if (null != $accessToken) {
            foreach ($requests as $k => $req) {
                /**
                 * @var $req JosRequest
                 */
                $req->setAccessToken($accessToken);
                $requests[$k] = $req;
            }
        }
        $responses = $this->performRequests($requests);
        if ($returnFirst) {
            return current($responses);
        }

        return $responses;
    }

    /**
     * @param array $requests
     *
     * @return array
     */
    protected function performRequests(array $requests = [])
    {
        $gwUrl = $this->httpGatewayUri;
        $hostNameOverRide = $this->httpHostnameOverride;
        foreach ($requests as $key => $request) {
            /** @var  $request JosRequest */
            if ($request->requireHttps || $this->forceHttps) {
                $gwUrl = $this->httpsGatewayUri;
                $hostNameOverRide = $this->httpsHostnameOverride;
            }
            $request->apiPath = $gwUrl;
            //签名
            $request->setQuery([
                                   'app_key'   => $this->appKey,
                                   'timestamp' => date("Y-m-d H:i:s"),
                               ], true);
            $request->setSign($this->signPara($request->getQuery()));
            \Log::channel('platform_api')->debug((string)$request);
            $psr7Request = $request->getRequest();
            if ($hostNameOverRide) {
                $psr7Request = $psr7Request->withHeader('Host', $hostNameOverRide);
            }
            $psr7Requests[$key] = $psr7Request;
        }
        $responses = $this->send($psr7Requests);
        foreach ($responses as $key => $response) {
            /** @var  $request JosRequest */
            $request = $requests[$key];
            $result = $this->parseResponse($response, $request->format);
            $responses[$key] = $result;
        }

        return $responses;
    }

    public function signPara($params)
    {
        unset($params['sign']);
        ksort($params);
        $stringToBeSigned = $this->appSecret;
        foreach ($params as $k => $v) {
            if (is_string($v) && "@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->appSecret;

        return strtoupper(md5($stringToBeSigned));
    }

    public function send($requests)
    {
        $adaptor = $this->app->make(GuzzleAdapter::class);

        return $adaptor->send($requests);
    }

    public function parseResponse(\Psr\Http\Message\ResponseInterface $response, $format = "json")
    {
        if ("json" === $format) {
            $decodedResponse = json_decode((string)$response->getBody(), true);
            if (null !== $decodedResponse) {
                $result = $decodedResponse;
            } else {
                throw new PlatformClientSideException('Invalid Json Response');
            }
        } elseif ("xml" === $format) {
            libxml_disable_entity_loader(true);
            $decodedResponse = @simplexml_load_string((string)$response->getBody());
            if (false !== $decodedResponse) {
                $result = json_decode(json_encode($decodedResponse), true);//把里面的Object对象转乘数组
            } else {
                throw new PlatformClientSideException('Invalid XML Response');
            }
        } else {
            throw new PlatformClientSideException('unknown format: ' . $format);
        }
        //启用json简洁返回并不能对错误信息生效
        if (isset($result['error_response'])) {
            $result = $result['error_response'];
        }
        if (!empty($result['code'])) {
            throw $this->getExceptionInstanceByResponse($result);
        }

        \Log::channel('platform_api')->debug('jos response', $result);
        return $result;
    }

    public function getExceptionInstanceByResponse(array $result)
    {
        \Log::channel('platform_api')->error('jos exception', [$result]);
        $message = $result['zh_desc'];
        $code = $result['code'];
        if (!is_int($code)) {
            $code = -1;
        }
        $class = $this->getExceptionClassByCode($result['code'], $result['sub_code'] ?? null);
        if (isset($result['sub_msg'])) {
            $message .= ': ';
            $message .= $result['sub_msg'];
        }
        if (isset($result['sub_code'])) {
            $message .= sprintf(' (%s / %s)', $result['code'], $result['sub_code']);
        }
        $instance = new $class($message, $code);
        if ($instance instanceof JosServerSideException) {
            $instance->setSubErrorCode($result['sub_code'] ?? null);
            $instance->setSubErrorMessage($result['sub_msg'] ?? null);
            $instance->setResponseBody($result);
        }

        return $instance;
    }

    public function getExceptionClassByCode($code, $subCode)
    {
        // 29 Invalid app Key
        // 25 Invalid signature
        switch ($code) {
            case 18:
            case 19: // access_token
                return PlatformTokenInvalidException::class;
            case 2:
            case 24://自定义 调试用
            case 32://操作太频繁
                return PlatformAppCallLimitedException::class;
            default:
                if ($subCode && (stripos($subCode, 'isp.') === 0)) {
                    $subCode = strtolower($subCode);
                    switch ($subCode) {
                        case 'isp.call-limited':
                            return PlatformAppCallLimitedException::class;
                        default:
                    }
                }

                return PlatformServerSideException::class;
        }
    }
}
