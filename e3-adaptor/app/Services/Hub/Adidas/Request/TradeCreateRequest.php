<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\TradeCreateTransformer;
use App\Services\Hub\Jobs\TradeInvoiceApplyInitJob;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 订单创建
 *
 * Class TradeCreateRequest
 * @package App\Services\Hub\Adidas\Request
 *
 * @author linqihai
 * @since 2020/1/6 17:47
 */
class TradeCreateRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'adidas/omnihub/createorder';

    public $keyword = '';

    public function getApiMethodName()
    {
        return $this->apiName;
    }

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdTrade = $id;
        } else {
            $stdTrade = SysStdTrade::where('tid', $id)->first();
        }
        // 设置时间戳
        $this->setDataVersion(strtotime($stdTrade['modified']));
        $this->keyword = $stdTrade['tid'] ?? '';
        $this->data = $this->getTransformer()->format($stdTrade);

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

        $stdTrade = SysStdTrade::where('tid', $id)->first();

        $this->setDataVersion(strtotime($stdTrade['modified']));
        $this->keyword = $id;
        if ($pushVersion && $pushVersion > 0 && $pushContent && ($pushVersion >= $this->dataVersion)) {
            $this->data = $pushContent;
        } else { // 调用 transformer
            $this->setContent($stdTrade);
        }

        return $this;
    }

    /**
     *
     * @return TradeCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(TradeCreateTransformer::class);
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
        // 推送成功之后，需要监听获取发票信息
        // dispatch(new TradeInvoiceApplyInitJob($this->keyword));
    }
}
