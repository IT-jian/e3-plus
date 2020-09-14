<?php


namespace App\Services\Platform\Taobao\Client\Top;


use App\Services\Platform\Taobao\Client\Top\Exceptions\TaobaoTopClientSideException;
use Illuminate\Support\Str;

class TopRequest extends BaseRequest
{
    ///----taobao specific--------------
    public $requireHttps = false;//是否必须使用https接口
    public $defaultHttpGatewayUri = "http://gw.api.taobao.com/router/rest";
    public $defaultHttpsGatewayUri = "https://eco.taobao.com/router/rest";
    public $extraParas = [];
    public $encryptedFields;
    public $format = "json";
    protected $paramKeys = [];
    protected $defaultParamValues = [];
    protected $commaSeparatedParams = [];

    public function __construct()
    {
        if (!$this->apiName) {
            throw new TaobaoTopClientSideException('RAW Request 必须指定合法API名称');
        }

        if (!empty($this->defaultParamValues)) {
            foreach ($this->defaultParamValues as $para => $value) {
                $this->data[Str::snake($para)] = (string)$value;
            }
        }
    }

    public static function maybeApiRequestDsn(string $string): bool
    {
        return strpos($string, '.') > 0;
    }

    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, 'set') || Str::startsWith($name, 'get')) {
            $clearName = Str::camel(substr($name, 3));
            $actionPrefix = substr($name, 0, 3);
            //            var_dump($this->paramKeys);
            if (in_array($clearName, $this->paramKeys)) {
                return call_user_func_array([$this, '__' . $actionPrefix], array_merge([$clearName], $arguments));
            }
        }
        throw new TaobaoTopClientSideException('不存在的方法调用: ' . $name);
    }

    public function __get($name)
    {
        if (in_array($name, $this->commaSeparatedParams)) {
            return $this->getCommaSeparatedParam($name);
        } elseif (in_array($name, $this->paramKeys)) {
            return $this->data[Str::snake($name)] ?? null;
        }
        throw new TaobaoTopClientSideException('指定属性不存在: ' . $name);
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->commaSeparatedParams)) {
            return $this->setCommaSeparatedParam($name, $value);
        } elseif (in_array($name, $this->paramKeys)) {
            if (!is_array($value)) {
                $value = (string)$value;
            }
            $this->setData([Str::snake($name) => $value], true);

            return $this;
        }
        throw new TaobaoTopClientSideException('指定的属性不可设置: ' . $name);
    }

    public function getCommaSeparatedParam($name)
    {
        $values = $this->data[$name] ?? '';

        return array_filter(explode(',', $values));
    }

    public function setCommaSeparatedParam($name, $value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $this->setData([Str::snake($name) => $value], true);

        return $this;
    }

    public function addCommaSeparatedParamsField($name, $value)
    {
        if (is_string($value)) {
            $value = array_filter(explode(',', $value));
        }
        $originalFields = $this->getCommaSeparatedParam($name);

        return $this->setCommaSeparatedParam($name, array_merge((array)$originalFields, $value));
    }

    public function getAccessToken()
    {
        return $this->getSession();
    }

    public function getSession()
    {
        return $this->data['session'] ?? null;
    }

    public function setAccessToken($accessToken)
    {
        return $this->setSession($accessToken);
    }

    public function setSession(string $session)
    {
        if (!is_string($session)) {
            unset($this->data['session']);
        } else {
            if ($this->method === 'GET' || $this->method === 'HEAD') {
                $this->setQuery(['session' => $session], true);
            } else {
                $this->setData(['session' => $session], true);
            }
        }

        return $this;
    }

    public function getQuery()
    {
        $query = parent::getQuery();

        return array_merge(is_array($query) ? $query : [], [
            'v'      => $this->apiVersion,
            'format' => $this->format,
            'method' => $this->getApiName(),
            //            'timestamp' => date("Y-m-d H:i:s"),
        ]);
    }

    public function getData()
    {
        $data = parent::getData();

        return (array)$data;
    }

    public function getApiPath()
    {
        if ($this->requireHttps) {
            $this->uriPlaceHolder['Uri'] = $this->defaultHttpsGatewayUri;
        } else {
            $this->uriPlaceHolder['Uri'] = $this->defaultHttpGatewayUri;
        }

        return parent::getApiPath();
    }

    public function setSign($sign)
    {
        $this->setQuery(['sign' => $sign], true);

        return $this;
    }
}
