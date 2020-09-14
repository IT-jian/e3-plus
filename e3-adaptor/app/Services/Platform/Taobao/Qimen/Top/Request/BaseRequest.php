<?php


namespace App\Services\Platform\Taobao\Qimen\Top\Request;


use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Psr7\Uri;

abstract class BaseRequest
{
    public $method = 'POST';
    public $apiBase = '';
    public $userAgent = 'top-sdk-php';
    public $apiPath = '{Uri}';
    public $requireHttps = false;//是否必须使用https接口
    public $defaultHttpGatewayUri = "http://gw.api.taobao.com/router/rest";
    public $defaultHttpsGatewayUri = "https://eco.taobao.com/router/rest";
    protected $apiParas;
    protected $query = [];
    protected $uriPlaceHolder = [];
    protected $contentType = 'form';
    protected $apiName;
    protected $apiVersion = "2.0";

    public function __toString()
    {
        $psrRequest = $this->getRequest();

        return self::psrRequestToString($psrRequest);
    }

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        $uri = $this->apiBase . $this->getApiPath();
        $uri = new Uri($uri);
        $data = $this->getData();
        $query = $this->getQuery();
        if ($query) {
            $uri = $uri->withQuery(http_build_query($query));
        }
        $request = (new Request($this->method, $uri))
            ->withHeader('user-agent', $this->userAgent);
        $contentType = $this->contentType;
        if ($data && $contentType) {
            if ($contentType === 'form') {
                $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8')
                    ->withBody(stream_for(is_array($data) ? http_build_query($data) : $data));
            } elseif ($contentType === 'multipart') {
                $multiPartData = $this->getMultiPartData($data);
                $stream = new MultipartStream($multiPartData);
                $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $stream->getBoundary())
                    ->withBody($stream);
            } elseif ($contentType === 'json') {
                $request = $request->withHeader('Content-Type', 'application/json')
                    ->withBody(is_string($data) ?: stream_for(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
            }
        }

        return $request;
    }

    public function getApiPath()
    {
        if ($this->requireHttps) {
            $this->uriPlaceHolder['Uri'] = $this->defaultHttpsGatewayUri;
        } else {
            $this->uriPlaceHolder['Uri'] = $this->defaultHttpGatewayUri;
        }

        if ($this->uriPlaceHolder && strpos($this->apiPath, '{') !== false) {
            return str_replace(array_keys($this->uriPlaceHolder), array_values($this->uriPlaceHolder), $this->apiPath);
        }

        return $this->apiPath;
    }

    public function getData()
    {
        return $this->apiParas;
    }

    public function getMultiPartData($data)
    {
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

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery(array $value, $merge = false)
    {
        $this->query = $merge ? array_merge($this->query, $value) : $value;

        return $this;
    }

    public static function psrRequestToString(\Psr\Http\Message\RequestInterface $psrRequest)
    {
        $requestString = sprintf("%s %s\r\n", $psrRequest->getMethod(), trim((string)$psrRequest->getUri()));
        foreach ($psrRequest->getHeaders() as $headerName => $headerValues) {
            foreach ($headerValues as $headerValue) {
                $requestString .= sprintf("%s: %s\r\n", $headerName, $headerValue);
            }
        }
        $requestString .= "\r\n";
        if ($body = $psrRequest->getBody()) {
            $requestString .= $body;
        }

        return $requestString;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    abstract public function getApiMethodName();

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function check()
    {

    }

    public function putOtherTextParam($key, $value) {
        $this->apiParas[$key] = $value;
        $this->$key = $value;
    }
}
