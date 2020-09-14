<?php


namespace App\Services\Hub\Adidas\JingdongRequest;


use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\Request\TradeCancelRequest as BaseRequest;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\TradeCancelTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 订单取消 - 通过退单触发
 * 订单发货之前申请退款，并且退款成功之后下发
 *
 * Class TradeCancelRequest
 * @package App\Services\Hub\Adidas\JingdongRequest
 */
class TradeCancelRequest extends BaseRequest
{
    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdTrade = $id;
        } else {
            $stdTrade = SysStdTrade::where('tid', $id)->first();
        }
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
     * @return TradeCancelTransformer
     */
    public function getTransformer()
    {
        return app()->make(TradeCancelTransformer::class);
    }
}
