<?php


namespace App\Services\Hub\Adidas\JingdongRequest\Transformer;


use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\Request\Transformer\RefundReturnCreateTransformer as BaseTransformer;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 退单创建 格式
 *
 * Class RefundReturnCreateTransformer
 * @package App\Services\Hub\Adidas\JingdongRequest\Transformer
 *
 */
class RefundReturnCreateTransformer extends BaseTransformer
{

    public function format($stdRefund)
    {
        // 判断是否加强版报文
        if (cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
            // 查询订单
            $where = [
                'tid' => $stdRefund['tid'],
                'platform' => $stdRefund['platform']
            ];
            $trade = SysStdTrade::where($where)->firstOrFail();
            return (new RefundReturnCreateExtendTransformer())->format($stdRefund, $trade);
        }

        $rootElement = $this->getRootElement($stdRefund);

        $content = $this->getContent($stdRefund);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    // 京东退货物流公司
    public function getShippingName($refund)
    {
        return '';
    }

}
