<?php


namespace App\Services\Hub\Adidas\JingdongRequest\Transformer;


use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\Request\Transformer\ExchangeCreateTransformer as BaseTransformer;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 换货单新增 格式
 *
 * Class ExchangeCreateTransformer
 * @package App\Services\Hub\Adidas\JingdongRequest\Transformer
 *
 */
class ExchangeCreateTransformer extends BaseTransformer
{
    public function format($stdExchange)
    {
        // 判断是否加强版报文
        if (cutoverTrade($stdExchange['tid'], $stdExchange['platform'])) {
            // 查询订单
            $where = [
                'tid' => $stdExchange['tid'],
                'platform' => $stdExchange['platform']
            ];
            $trade = SysStdTrade::where($where)->firstOrFail();

            return (new ExchangeCreateExtendTransformer())->format($stdExchange, $trade);
        }

        $rootElement = $this->getRootElement();
        $content = $this->getContent($stdExchange);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    public function shippingInfo($exchange)
    {
        // 京东换货单地址，取原单地址
        return [];
    }
}
