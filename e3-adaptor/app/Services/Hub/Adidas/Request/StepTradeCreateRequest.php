<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\StepTradeCreateTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 预售订单创建
 *
 * Class StepTradeCreateRequest
 * @package App\Services\Hub\Adidas\Request
 *
 * @author linqihai
 * @since 2020/1/6 17:47
 */
class StepTradeCreateRequest extends BaseRequest implements RequestContract
{
    public $keyword = '';
    protected $apiName = 'adidas/omnihub/createorder';

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdTrade = $id;
        } else {
            $stdTrade = SysStdTrade::where('tid', $id)->first();
        }
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
     * @return StepTradeCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(StepTradeCreateTransformer::class);
    }
}