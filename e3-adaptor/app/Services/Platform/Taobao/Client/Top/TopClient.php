<?php


namespace App\Services\Platform\Taobao\Client\Top;


use App\Services\Platform\Taobao\Client\Top\Request\RequestContract;
use Exception;

class TopClient
{
    public $appkey;
    public $secretKey;
    public $accessToken;
    public $gatewayUrl = "http://gw.api.taobao.com/router/rest";
    public $format = "json";
    public $connectTimeout;
    public $readTimeout;
    /** 是否打开入参check**/
    public $checkRequest = true;
    protected $signMethod = "hmac";
    protected $apiVersion = "2.0";
    protected $sdkVersion = "top-sdk-php-20180326";

    public function __construct($appkey = "", $secretKey = "")
    {
        $this->appkey = $appkey;
        $this->secretKey = $secretKey;
    }

    public function shop($shop)
    {
        $this->appkey = $shop['app_key'];
        $this->secretKey = $shop['app_secret'];
        $this->accessToken = $shop['access_token'];

        return $this;
    }

    public function execute(RequestContract $request, $session = null, $bestUrl = null)
    {
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
        if (null != $session) {
            $sysParams["session"] = $session;
        }
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
        \Log::channel('platform_api')->debug($requestUrl, $apiParams);
        //发起HTTP请求
        try {
            if (count($fileFields) > 0) { // 上传内容
                $resp = $this->curl_with_memory_file($requestUrl, $apiParams, $fileFields);
            } else { // 接口请求
                $resp = $this->curl($requestUrl, $apiParams);
            }
        } catch (Exception $e) {
            $this->logCommunicationError($sysParams["method"], $requestUrl, "HTTP_ERROR_" . $e->getCode(), $e->getMessage());
            $result->code = $e->getCode();
            $result->msg = $e->getMessage();

            return $result;
        }
        \Log::channel('platform_api')->debug($resp);
        unset($apiParams);
        unset($fileFields);
        //解析TOP返回结果
        $respWellFormed = false;
        if ("json" == $this->format) {
            $respObject = json_decode($resp, true, 512, JSON_BIGINT_AS_STRING);
            \Log::error('top client response', [$respObject]);
            if (null !== $respObject) {
                $respWellFormed = true;
                /*foreach ($respObject as $propKey => $propValue) {
                    $respObject = $propValue;
                }*/
            }
        } else if ("xml" == $this->format) {
            $respObject = @simplexml_load_string($resp);
            if (false !== $respObject) {
                $respWellFormed = true;
            }
        }
        //返回的HTTP文本不是标准JSON或者XML，记下错误日志
        if (false === $respWellFormed) {
            $result->code = 0;
            $result->msg = "HTTP_RESPONSE_NOT_WELL_FORMED";

            return $result;
        }
        //如果TOP返回了错误码，记录到业务错误日志中
        if (isset($respObject->code)) {
            // @todo token 失效错误码，处理刷新 token
        }

        return $respObject;
    }

    private function getClusterTag()
    {
        return substr($this->sdkVersion, 0, 11) . "-cluster" . substr($this->sdkVersion, 11);
    }

    protected function generateSign($params)
    {
        $sign = '';
        ksort($params);
        foreach ($params as $key=>$value) {
            if ('' == $key || !isset($value)) {
                continue;
            }
            if("@" == substr($value, 0, 1)) {
                continue;
            }
            if (is_array($value) && count($value) >= 1) {
                $itemarr = array();
                foreach ($value as $_value) {
                    $sign .= $key . $_value;
                }
            } else {
                $sign .= $key . $value;
            }
        };

        $app_secret = $this->secretKey;
        if (isset($this->signMethod) && $this->signMethod === 'hmac') {
            $sign = strtoupper(hash_hmac('md5', $sign, $app_secret));
        } else {
            $sign = strtoupper(md5($app_secret . $sign . $app_secret));
        }

        return $sign;
    }

    /**
     * 上传文件
     * @param $url
     * @param null $postFields
     * @param null $fileFields
     * @return mixed
     * @throws Exception
     */
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
            throw new \Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new \Exception($reponse, $httpStatusCode);
            }
        }
        curl_close($ch);

        return $reponse;
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
                if (!is_string($v))
                    continue;
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
            throw new \Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new \Exception($reponse, $httpStatusCode);
            }
        }
        curl_close($ch);

        return $reponse;
    }

    public function logCommunicationError($method, $url, $code, $msg)
    {
        \Log::error('request taobao api communication error', compact('method', 'url', 'code', 'msg'));
    }
}
