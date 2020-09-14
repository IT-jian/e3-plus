<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 14 - 库存同步
 *
 * Class StockWriteUpdateSkuStock
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setSkuId($value) sku编号
 * @method $this setStockNum($value) 库存数量
 *
 * jingdong_stock_write_updateSkuStock_response.success
 */
class StockWriteUpdateSkuStock extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://jos.jd.com/api/detail.htm?apiName=jingdong.asc.receive.list&id=2114
     */
    protected $apiName = 'jingdong.stock.write.updateSkuStock';

    protected $paramKeys = [
        'skuId',
        'stockNum',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->skuId, "skuId");
        //RequestCheckUtil::checkNotNull($this->stockNum, "stockNum");
    }
}