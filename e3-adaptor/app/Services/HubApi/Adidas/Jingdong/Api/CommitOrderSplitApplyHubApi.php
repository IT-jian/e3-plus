<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Models\AdidasItem;
use App\Models\Sys\Shop;
use App\Models\SysStdTradeItem;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Jingdong\Client\Jos\Request\CommitOrderSplitApplyApiRequest;
use Illuminate\Support\Str;

/**
 * 10 - 京东拆单申请
 *
 * Class CommitOrderSplitApplyHubApi
 * {"param":{'orderId':11378607792,'venderId':null,'skuGroup':[{'skuInfoList': [{'skuId':1722759947,'num':1}],'groupContent':null},{'skuInfoList':[{'skuId':1466911848,'num':1}, {'skuId':1452876009,'num':1}],'groupContent':null}],'systemId':null,'systemName':null,'type':0}}
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 */
class CommitOrderSplitApplyHubApi extends BaseApi
{
    public function proxy()
    {

        $shop = Shop::where('code', $this->data['shop_code'])->firstOrFail();

        $skuGroup = [];
        $request = new CommitOrderSplitApplyApiRequest();
        $splitList = $this->data['split_list'];
        foreach ($splitList as $list) {
            $skuInfoList = [];
            foreach ($list as $item) {
                $skuId = $this->mapPlatformSkuId($item['sku_id']);
                $skuInfoList[] = [
                    'skuId' => $skuId,
                    'num'   => $item['num'],
                ];
            }
            $skuGroup[] = [
                'skuInfoList' => $skuInfoList,
                'groupContent' => null
            ];
        }
        $param = [
            'orderId' => $this->data['deal_code'],
            'venderId' => $shop['seller_nick'],
            'skuGroup' => $skuGroup,
            'systemId' => null,
            'systemName' => '',
            'type' => 0
        ];
        $request->setParam($param);

        $result = JosClient::shop($shop['code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function mapPlatformSkuId($outerId)
    {
        if (Str::contains($outerId, ['_'])) {
            $outerSkuArr = explode('_', $outerId);
            $where = [
                ['item_id', $outerSkuArr[0]],
                ['size', $outerSkuArr[1]],
            ];
            $adidasItem = AdidasItem::where($where)->firstOrFail(['outer_sku_id']);
            $outerSkuId = $adidasItem['outer_sku_id'];
            $tradeItem = SysStdTradeItem::select(['sku_id'])->where('platform', 'jingdong')->where('tid', $this->data['deal_code'])->whereIn('outer_sku_id', [$outerSkuId, $outerId])->firstOrFail();
        } else {
            $outerSkuId = $outerId;
            $tradeItem = SysStdTradeItem::select(['sku_id'])->where('platform', 'jingdong')->where('tid', $this->data['deal_code'])->where('outer_sku_id', $outerSkuId)->firstOrFail();
        }

        return $tradeItem['sku_id'];
    }

    public function isSuccess($response)
    {
        return data_get($response, 'jingdong_commitOrderSplitApplyApi_responce.apiSafResult.success', false);
    }

    /**
     * 处理报错
     *
     * @param $response
     * @return int|void
     */
    public function parseErrorCode($response)
    {
        $errorCode = data_get($response, 'jingdong_commitOrderSplitApplyApi_responce.apiSafResult.resultCode', false);
        if ($errorCode && in_array($errorCode, ['009'])) {
            return self::ERROR_CODE_RETRY;
        }

        return self::ERROR_CODE_FAIL;
    }
}
