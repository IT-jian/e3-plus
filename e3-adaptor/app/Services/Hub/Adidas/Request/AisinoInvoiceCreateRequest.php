<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdTrade;
use App\Models\TaobaoInvoiceApply;
use App\Services\Hub\Adidas\BaseRequest;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

/**
 * 发票创建
 *
 * Class InvoiceQueryRequest
 * @package App\Services\Hub\Adidas\Request
 */
class AisinoInvoiceCreateRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'invoice/aisino/create';

    public $format = 'json';

    public $keyword = '';

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $invoiceApply = $id;
        } else {
            $invoiceApply = TaobaoInvoiceApply::where('apply_id', $id)->first();
        }
        $this->setDataVersion(strtotime($invoiceApply['updated_at']));
        $this->keyword = $invoiceApply['platform_tid'] ?? '';
        $this->data = $this->getTransformer()->format($invoiceApply);

        return $this;
    }

    /**
     * 推送 -- 已经格式化的内容
     *
     * @param $params
     * @return $this
     *
     * @author linqihai
     * @since 2020/05/25 14:40
     */
    public function setFormatContent($params)
    {
        $id = $params->bis_id;
        $pushVersion = $params->push_version ?? 0;
        $pushContent = $params->push_content ?? '';

        $invoiceApply = TaobaoInvoiceApply::where('apply_id', $id)->first();

        $this->setDataVersion(strtotime($invoiceApply['updated_at']));
        $this->keyword = $id;
        if ($pushVersion && $pushVersion > 0 && $pushContent && ($pushVersion >= $this->dataVersion)) {
            $this->data = $pushContent;
        } else { // 调用 transformer
            $this->setContent($invoiceApply);
        }

        return $this;
    }

    /**
     *
     * @return AisinoInvoiceCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(AisinoInvoiceCreateTransformer::class);
    }

    public function getRequest()
    {
        $config = config('hubclient.aisino', []);
        $signPwd = $config['sign_pwd'];

        $key = substr($signPwd, 0, 8);
        $vi = substr($signPwd, -8);

        $uri = data_get($config, 'url', '');
        $uri = new Uri($uri);
        $data = [
            'HEADERS' => [
                'CONSUMER_SYSCODE' => data_get($config, 'consumer_syscode', ''),
                'SIGN_PWD' => data_get($config, 'sign_pwd', ''),
                'DTSEND' => Carbon::now()->format('YmdHis'),
            ],
            'DATA' => self::encrypt(json_encode($this->getBody()), $key, $vi),
        ];
        $headers = [];
        $request = (new Request('POST', $uri, $headers, $data));

        return $request;
    }


    /**
     * @desc 加密返回十六进制字符串
     * @param string $input
     * @param string $key 加密使用的密钥
     * @param string $vi 加密使用的向量
     * @return string
     */
    final static public function encrypt($input, $key, $vi)
    {
        return bin2hex(openssl_encrypt($input, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $vi));
    }

    /**
     * @param string $crypt 需要解密的字符串
     * @param string $key 加密使用的密钥
     * @param string $vi 加密使用的向量
     * @return string $input 解密后的字符串
     * @des 3DES解密
     */
    final static public function decrypt($crypt, $key, $vi)
    {
        return openssl_decrypt(hex2bin($crypt), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $vi);
    }
}
