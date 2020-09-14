<?php


namespace App\Services\Adaptor\Jingdong\Transformer;


use App\Models\JingdongTrade;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Jingdong\Repository\JingdongStepTradeRepository;
use Exception;
use InvalidArgumentException;

class StepTradeTransformer implements TransformerContract
{
    const PLATFORM = 'jingdong';

    /**
     * @var string
     */
    protected $shopCode;

    /**
     * @var JingdongStepTradeRepository
     */
    protected $trade;

    public function __construct(JingdongStepTradeRepository $trade)
    {
        $this->trade = $trade;
    }

    /**
     * 单个转入
     *
     * @param $params
     * @return bool
     * @throws Exception
     *
     */
    public function transfer($params)
    {
        if (empty($params['order_id'])) {
            throw new InvalidArgumentException('order_id required!');
        }

        $shopCode = $params['shop_code'] ?? '';
        $orderId = $params['order_id'];
        $jingdongTrade = JingdongTrade::find($orderId);
        if (empty($jingdongTrade)){
            return false;
        }
        if (empty($shopCode)) {
            $shop = Shop::select('code')->where('seller_nick', $jingdongTrade['vender_id'])->first();
            $shopCode = $shop['code'];
        }
        if (empty($shopCode)) {
            throw new Exception('店铺不存在' . $jingdongTrade['vender_id']);
        }
        $this->shopCode = $shopCode;

        return true;
    }
}
