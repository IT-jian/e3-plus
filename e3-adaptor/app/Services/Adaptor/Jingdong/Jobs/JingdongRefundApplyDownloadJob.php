<?php


namespace App\Services\Adaptor\Jingdong\Jobs;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\RefundApplyQuery;

class JingdongRefundApplyDownloadJob extends BaseDownloadJob
{

    private $params;

    /**
     * JingdongStepTradeTransferJob constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $shop = Shop::where('code', $this->params['shop_code'])->firstOrFail();
        $refundApi = new RefundApplyQuery($shop);
        $refunds = $refundApi->find($this->params);
        foreach ($refunds as $refund) {
            $refund['vender_id'] = $shop['seller_nick'];
        }
        return Adaptor::platform('jingdong')->download(AdaptorTypeEnum::REFUND_APPLY, $refunds);
    }
}