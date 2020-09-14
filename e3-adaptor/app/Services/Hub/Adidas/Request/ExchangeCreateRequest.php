<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdExchange;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\ExchangeCreateTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 换货单创建
 *
 * Class ExchangeCreateRequest
 * @package App\Services\Hub\Adidas\Request
 *
 * @author linqihai
 * @since 2020/1/6 17:55
 */
class ExchangeCreateRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'eai/baison/returnorderexport';

    public $keyword = '';

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdExchange = $id;
        } else {
            $stdExchange = SysStdExchange::where('dispute_id', $id)->first();
        }
        $this->setDataVersion(strtotime($stdExchange['modified']));
        $this->keyword = $stdExchange['dispute_id'] ?? '';
        $this->data = $this->getTransformer()->format($stdExchange);

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

        $stdExchange = SysStdExchange::where('dispute_id', $id)->first();

        $this->setDataVersion(strtotime($stdExchange['modified']));
        $this->keyword = $id;
        if ($pushVersion && $pushVersion > 0 && $pushContent && ($pushVersion >= $this->dataVersion)) {
            $this->data = $pushContent;
        } else { // 调用 transformer
            $this->setContent($stdExchange);
        }

        return $this;
    }

    /**
     *
     * @return ExchangeCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(ExchangeCreateTransformer::class);
    }
}