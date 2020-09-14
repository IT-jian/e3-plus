<?php


namespace App\Services\Platform\Taobao\Qimen\Top;


use App\Models\Sys\Shop;
use App\Services\Platform\HttpClient\GuzzleAdapter;
use Exception;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Psr7\Uri;

class QimenCloudClient
{
    public $appkey;

    public $secretKey;

    public $accessToken;

    public $targetAppkey = "";

    public $gatewayUrl = null;

    public $format = "json";

    public $connectTimeout;

    public $readTimeout;

    /** 是否打开入参check**/
    public $checkRequest = false;

    protected $signMethod = "md5";

    protected $apiVersion = "2.0";

    protected $sdkVersion = "top-sdk-php-20151012";

    protected $config;

    public $method = 'POST';

    public function getAppkey()
    {
        return $this->appkey;
    }

    public function __construct($config)
    {
        $this->config = $config;
        // 执行初始化事件
        $this->onInitialize();
    }

    public function onInitialize()
    {
        $this->targetAppkey = $this->config['target_app_key'];
        $this->gatewayUrl = $this->config['gateway_url'];
    }

    public function shop($shop)
    {
        if (is_string($shop)) {
            $shop = Shop::getShopByCode($shop);
        }
        $this->appkey = $shop['app_key'];
        $this->secretKey = $shop['app_secret'];
        $this->accessToken = $shop['access_token'];

        return $this;
    }

    protected function generateSign($params)
    {
        ksort($params);

        $stringToBeSigned = $this->secretKey;
        foreach ($params as $k => $v) {
            if (!is_array($v) && "@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->secretKey;

        return strtoupper(md5($stringToBeSigned));
    }

    public function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
        }
        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "top-sdk-php");
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if (is_array($postFields) && 0 < count($postFields)) {
            $postBodyString = "";
            $postMultipart = false;
            foreach ($postFields as $k => $v) {
                if ("@" != substr($v, 0, 1))//判断是不是文件上传
                {
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                } else//文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                    if (class_exists('\CURLFile')) {
                        $postFields[$k] = new \CURLFile(substr($v, 1));
                    }
                }
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart) {
                if (class_exists('\CURLFile')) {
                    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
                } else {
                    if (defined('CURLOPT_SAFE_UPLOAD')) {
                        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            } else {
                $header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            }
        }
        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new Exception($reponse, $httpStatusCode);
            }
        }
        curl_close($ch);

        return $reponse;
    }

    public function curl_with_memory_file($url, $postFields = null, $fileFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
        }
        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "top-sdk-php");
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //生成分隔符
        $delimiter = '-------------' . uniqid();
        //先将post的普通数据生成主体字符串
        $data = '';
        if ($postFields != null) {
            foreach ($postFields as $name => $content) {
                $data .= "--" . $delimiter . "\r\n";
                $data .= 'Content-Disposition: form-data; name="' . $name . '"';
                //multipart/form-data 不需要urlencode，参见 http:stackoverflow.com/questions/6603928/should-i-url-encode-post-data
                $data .= "\r\n\r\n" . $content . "\r\n";
            }
            unset($name, $content);
        }

        //将上传的文件生成主体字符串
        if ($fileFields != null) {
            foreach ($fileFields as $name => $file) {
                $data .= "--" . $delimiter . "\r\n";
                $data .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $file['name'] . "\" \r\n";
                $data .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";//多了个文档类型

                $data .= $file['content'] . "\r\n";
            }
            unset($name, $file);
        }
        //主体结束的分隔符
        $data .= "--" . $delimiter . "--";

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                           'Content-Type: multipart/form-data; boundary=' . $delimiter,
                           'Content-Length: ' . strlen($data),
                       )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $reponse = curl_exec($ch);
        unset($data);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new Exception($reponse, $httpStatusCode);
            }
        }
        curl_close($ch);

        return $reponse;
    }

    protected function logCommunicationError($apiName, $requestUrl, $errorCode, $responseTxt)
    {
        $localIp = isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "CLI";
        $logData = array(
            date("Y-m-d H:i:s"),
            $apiName,
            $this->appkey,
            $localIp,
            PHP_OS,
            $this->sdkVersion,
            $requestUrl,
            $errorCode,
            str_replace("\n", "", $responseTxt),
        );
        \Log::error('request taobao api communication error', $logData);
    }

    public function execute($request, $session = null, $bestUrl = null)
    {
        if ($this->gatewayUrl == null) {
            throw new Exception("client-check-error:Need Set gatewayUrl.", 40);
        }

        \Log::error('qimen shop', [$session, $this->accessToken]);
        if (empty($session) && $this->accessToken){
            $session = $this->accessToken;
        }

        if (is_array($request)) {
            return $this->executes($request, $session, $bestUrl);
        }
        $result = new ResultSet();
        if ($this->checkRequest) {
            try {
                $request->check();
            } catch (Exception $e) {

                $result->code = $e->getCode();
                $result->msg = $e->getMessage();

                return $result;
            }
        }
        //组装系统参数
        $sysParams["app_key"] = $this->appkey;
        $sysParams["v"] = $this->apiVersion;
        $sysParams["format"] = $this->format;
        $sysParams["sign_method"] = $this->signMethod;
        $sysParams["method"] = $request->getApiMethodName();
        $sysParams["timestamp"] = date("Y-m-d H:i:s");
        $sysParams["target_app_key"] = $this->targetAppkey;
        if (null != $session) {
            $sysParams["session"] = $session;
        }
        $apiParams = array();
        //获取业务参数
        $apiParams = $request->getApiParas();

        //系统参数放入GET请求串
        if ($bestUrl) {
            $requestUrl = $bestUrl . "?";
            $sysParams["partner_id"] = $this->getClusterTag();
        } else {
            $requestUrl = $this->gatewayUrl . "?";
            $sysParams["partner_id"] = $this->sdkVersion;
        }
        //签名
        $sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams));

        foreach ($sysParams as $sysParamKey => $sysParamValue) {
            // if(strcmp($sysParamKey,"timestamp") != 0)
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }

        $fileFields = array();
        foreach ($apiParams as $key => $value) {
            if (is_array($value) && array_key_exists('type', $value) && array_key_exists('content', $value)) {
                $value['name'] = $key;
                $fileFields[$key] = $value;
                unset($apiParams[$key]);
            }
        }

        // $requestUrl .= "timestamp=" . urlencode($sysParams["timestamp"]) . "&";
        $requestUrl = substr($requestUrl, 0, -1);

        //发起HTTP请求
        try {
            if (count($fileFields) > 0) {
                $resp = $this->curl_with_memory_file($requestUrl, $apiParams, $fileFields);
            } else {
                $resp = $this->curl($requestUrl, $apiParams);
            }
        } catch (Exception $e) {
            $this->logCommunicationError($sysParams["method"], $requestUrl, "HTTP_ERROR_" . $e->getCode(), $e->getMessage());
            $result->code = $e->getCode();
            $result->msg = $e->getMessage();

            return $result;
        }

        unset($apiParams);
        unset($fileFields);
        //解析TOP返回结果
        $respWellFormed = false;
        if ("json" == $this->format) {
            $respObject = json_decode($resp, true);
            if (null !== $respObject) {
                $respWellFormed = true;
                foreach ($respObject as $propKey => $propValue) {
                    $respObject = $propValue;
                }
            }
        } else if ("xml" == $this->format) {
            $respObject = @simplexml_load_string($resp);
            if (false !== $respObject) {
                $respWellFormed = true;
            }
        }

        //返回的HTTP文本不是标准JSON或者XML，记下错误日志
        if (false === $respWellFormed) {
            $this->logCommunicationError($sysParams["method"], $requestUrl, "HTTP_RESPONSE_NOT_WELL_FORMED", $resp);
            $result->code = 0;
            $result->msg = "HTTP_RESPONSE_NOT_WELL_FORMED";

            return $result;
        }

        //如果TOP返回了错误码，记录到业务错误日志中
        if (isset($respObject->code)) {
            \Log::error('', array(
                "top_biz_err_" . $this->appkey,
                date("Y-m-d H:i:s"),
                $resp,
            ));
        }

        return $respObject;
    }

    private function getClusterTag()
    {
        return substr($this->sdkVersion, 0, 11) . "-cluster" . substr($this->sdkVersion, 11);
    }

    /**
     * 批量请求
     *
     * @param $requests
     * @param null $session
     * @param null $bestUrl
     * @return array
     */
    public function executes($requests, $session = null, $bestUrl = null)
    {
        $psr7Requests = $responses = $results = [];

        if (empty($session) && $this->accessToken){
            $session = $this->accessToken;
        }

        foreach ($requests as $key => $request) {
            if ($this->checkRequest) {
                try {
                    $request->check();
                } catch (Exception $e) {
                    $result = new ResultSet();
                    $result->code = $e->getCode();
                    $result->msg = $e->getMessage();

                    $results[$key] = $result;
                    continue;
                }
            }
            $psr7Requests[$key] = $this->getRequest($request, $session, $bestUrl);
        }

        if (!empty($psr7Requests)) {
            \Log::info('request qimen', [$psr7Requests]);
            $responses = $this->send($psr7Requests);
            \Log::info('qimen response', [$responses]);
            foreach ($responses as $key => $resp) {
                $results[$key] = $this->parseResponse($resp);
            }
        }

        return $results;
    }

    public function send($psr7Requests)
    {
        return app()->make(GuzzleAdapter::class)->send($psr7Requests);
    }
    /**
     * 获取 psr7 请求
     *
     * @param $request
     * @param null $session
     * @param null $bestUrl
     * @return \GuzzleHttp\Psr7\MessageTrait
     */
    public function getRequest($request, $session = null, $bestUrl = null)
    {
        //组装系统参数
        $sysParams = [];
        $sysParams["app_key"] = $this->appkey;
        $sysParams["v"] = $this->apiVersion;
        $sysParams["format"] = $this->format;
        $sysParams["sign_method"] = $this->signMethod;
        $sysParams["method"] = $request->getApiMethodName();
        $sysParams["timestamp"] = date("Y-m-d H:i:s");
        $sysParams["target_app_key"] = $this->targetAppkey;

        if (empty($session) && $this->accessToken){
            $session = $this->accessToken;
        }

        if (null != $session) {
            $sysParams["session"] = $session;
        }
        $apiParams = array();
        //获取业务参数
        $apiParams = $request->getApiParas();

        //系统参数放入GET请求串
        if ($bestUrl) {
            $requestUrl = $bestUrl . "?";
            $sysParams["partner_id"] = $this->getClusterTag();
        } else {
            $requestUrl = $this->gatewayUrl . "?";
            $sysParams["partner_id"] = $this->sdkVersion;
        }

        //签名
        $sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams));
        \Log::debug('qimen request' . $requestUrl, [$sysParams, $apiParams]);
        $uri = new Uri($requestUrl);
        $uri = $uri->withQuery(http_build_query($sysParams));
        $psr7Request = (new Request($this->method, $uri))
            ->withHeader('user-agent', 'top-sdk-php');
        $multipart = false;
        foreach ($apiParams as $key => $value) {
            if (!is_array($value) && "@" == substr($value, 0, 1)) {
                $multipart = true;
            }
        }

        if ($multipart) {
            $multiPartData = $this->getMultiPartData($apiParams);
            $stream = new MultipartStream($multiPartData);
            $psr7Request = $psr7Request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $stream->getBoundary())
                ->withBody($stream);
        } else {
            $psr7Request = $psr7Request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8')
                ->withBody(stream_for(is_array($apiParams) ? http_build_query($apiParams) : $apiParams));
        }

        return $psr7Request;
    }

    public function getMultiPartData($data)
    {
        $multiPart = [];
        foreach ($data as $k => $v) {
            if ("@" == substr($v, 0, 1))//判断是不是文件上传
            {
                $multiPart[] = [
                    'name'     => $k,
                    'contents' => fopen(substr($v, 1), 'r'), // 读取文件
                ];
            } else {
                $multiPart[] = [
                    'name'     => $k,
                    'contents' => $v,
                ];
            }
        }
        unset($k, $v);

        return $multiPart;
    }

    /**
     * @param $resp
     * @return ResultSet|mixed|\SimpleXMLElement
     */
    public function parseResponse($response)
    {
        //解析TOP返回结果
        $respWellFormed = false;
        $respObject = '';
        if ("json" == $this->format) {
            $respObject = json_decode((string)$response->getBody(), true);
            if (null !== $respObject) {
                $respWellFormed = true;
                foreach ($respObject as $propKey => $propValue) {
                    $respObject = $propValue;
                }
            }
        } else if ("xml" == $this->format) {
            $respObject = @simplexml_load_string((string)$response->getBody());
            if (false !== $respObject) {
                $respWellFormed = true;
            }
        }

        //返回的HTTP文本不是标准JSON或者XML，记下错误日志
        if (false === $respWellFormed) {
            $result = new ResultSet();
            $result->code = 0;
            $result->msg = "HTTP_RESPONSE_NOT_WELL_FORMED";

            return $result;
        }

        //如果TOP返回了错误码，记录到业务错误日志中
        if (isset($respObject->code)) {
            \Log::error('qimen error', array(
                "top_biz_err_" . $this->appkey,
                date("Y-m-d H:i:s"),
                (string)$response->getBody(),
            ));
        }

        return $respObject;
    }
}
