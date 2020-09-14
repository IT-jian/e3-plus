<?php


namespace App\Services\Hub\Adidas;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use function GuzzleHttp\Psr7\stream_for;

abstract class BaseRequest
{
    public $method = 'POST';
    public $apiBase = '';
    public $userAgent = 'adaptor-sdk-php';
    public $apiPath = '';
    protected $data;
    public $keyword = '';
    protected $query = [];
    protected $headers = [];
    protected $uriPlaceHolder = [];
    protected $contentType = 'form';
    protected $apiName;
    protected $apiVersion = "1.0";
    protected $dataVersion = "0"; // 请求的版本，用于版本控制，时间戳
    public $format = "xml";

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
        if (empty($this->apiPath)) {
            $config = config('hubclient.clients.adidas');
            $this->apiPath = $config['url'];
        }

        $uri = $this->apiBase . $this->apiPath;
        $uri = new Uri($uri);
        $data = $this->getData();
        $query = $this->getQuery();
        if ($query) {
            $uri = $uri->withQuery(http_build_query($query));
        }

        $request = (new Request($this->method, $uri, $this->getHeaders()))
            ->withHeader('Method', $this->apiName);
        if ($data) {
            $request = $request->withBody(stream_for(is_array($data) ? http_build_query($data) : $data));
        }

        return $request;
    }

    public function getData()
    {
        //merge me with api/version or name if needed
        return $this->data;
    }

    public function setData($value, $merge = false)
    {
        if (is_array($value) && $merge) {
            $this->data = array_merge((array)$this->data, $value);
        } else {
            $this->data = $value;
        }

        return $this;
    }

    public function getQuery()
    {
        //merge me with api/version or name if needed
        return $this->query;
    }

    public function setQuery(array $value, $merge = false)
    {
        $this->query = $merge ? array_merge($this->query, $value) : $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
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

    public function getApiName()
    {
        return $this->apiName;
    }

    public function setApiName($name)
    {
        $this->apiName = $name;

        return $this;
    }

    public function addDataArray(array $value)
    {
        return $this->setData($value, true);
    }

    /**
     * @return string
     */
    public function getDataVersion(): string
    {
        return $this->dataVersion;
    }

    /**
     * @param string $dataVersion
     */
    public function setDataVersion(string $dataVersion): void
    {
        $this->dataVersion = $dataVersion;
    }

    public function getApiMethodName()
    {
        return $this->apiName;
    }

    public function getBody()
    {
        return $this->data;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * 请求完成后回调
     *
     * @param array $parsedData
     *
     * @return bool
     */
    public function responseCallback($parsedData)
    {
        return true;
    }
}
