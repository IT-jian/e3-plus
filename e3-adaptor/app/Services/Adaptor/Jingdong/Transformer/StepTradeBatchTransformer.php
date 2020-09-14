<?php


namespace App\Services\Adaptor\Jingdong\Transformer;


use App\Models\JingdongTrade;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Jingdong\Repository\JingdongStepTradeRepository;
use Exception;
use InvalidArgumentException;

class StepTradeBatchTransformer extends StepTradeTransformer implements TransformerContract
{

    /**
     * 批量转入
     *
     * @param $params
     * @return bool
     * @throws Exception
     *
     */
    public function transfer($params)
    {
        if (empty($params['order_ids'])) {
            throw new InvalidArgumentException('order_ids required!');
        }
        $where = [];
        $orderIds = $params['order_ids'];
        $where[] = ['order_id', 'IN', $orderIds];
        $jingdongTrades = $this->trade->getAll($where);
        if (empty($jingdongTrades)) {
            throw new InvalidArgumentException('订单数据不存在!');
        }

        $sellerNickShopCodeMap = [];
        $shops = Shop::available(self::PLATFORM)->get();
        foreach ($shops as $shop) {
            $sellerNickShopCodeMap[$shop['seller_nick']] = $shop['code'];
        }
        if (empty($sellerNickShopCodeMap)) {
            throw new Exception('店铺不存在，请添加店铺后执行');
        }

        return true;
    }
}
