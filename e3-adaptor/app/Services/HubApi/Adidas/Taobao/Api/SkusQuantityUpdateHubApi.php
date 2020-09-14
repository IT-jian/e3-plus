<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Exceptions\PlatformServerSideException;
use App\Services\Platform\Taobao\Client\Top\Request\SkusQuantityUpdateRequest;
use Illuminate\Support\Str;

/**
 * 10-库存更新
 *
 * Class SkusQuantityUpdateHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/4/29 11:24
 */
class SkusQuantityUpdateHubApi extends BaseApi
{
    // 必填字段
    protected $notNullFields = ['data', 'shop_code'];

    public function proxy()
    {
        // 还没上线之前，用参数判断
        if (0 == config('hubapi.sku_update_api_batch', 0)) {
            $requests = $this->getRequest();
            $result = TopClient::shop($this->data['shop_code'])->execute($requests);

            if ($this->isSuccess($result)) {
                $responseData = $responseSuccess = [];
                foreach ($result as $response) {
                    $responseSuccess = data_get($response, 'skus_quantity_update_response.item.skus.sku', []);
                    $responseData = array_merge($responseSuccess, $responseData);
                }

                return $this->success(array_values($responseData));
            }

            return $this->fail($result);
        }

        $responseData = $successSkuIds = [];
        $requests = $this->getRequest();
        $errorBody = [];
        try {
            $result = TopClient::shop($this->data['shop_code'])->execute($requests);
        } catch (\Exception $e) {
            if ($e instanceof PlatformServerSideException) {
                $errorBody = $e->getResponseBody();
            }
            $result = [];
        }
        foreach ($result as $key => $response) {
            $responseResult = data_get($response, 'skus_quantity_update_response.item.skus.sku', []);
            if ($responseResult) {
                foreach ($responseResult as $item) {
                    $successSkuIds[] = $item['sku_id'];
                }
            }
        }
        // 非成功的都当成失败
        foreach ($this->data['data'] as $item) {
            if (in_array($item['sku_id'], $successSkuIds)) {
                $responseData[] = [
                    'Platform_sku_id' => $item['sku_id'],
                    'Platform_Response' => '1',
                ];
            } else {
                $responseData[] = [
                    'Platform_sku_id' => $item['sku_id'],
                    'Platform_Response' => '0',
                    'msg' => $errorBody['msg'] ?? '',
                    'sub_code' => $errorBody['sub_code'] ?? '',
                ];
            }
        }

        return $this->success(['responses' => $responseData]);
    }


    public function getRequest()
    {
        $updateNumIids = array();
        foreach ($this->data['data'] as $item) {
            $skuId = $item['sku_id'];
            $quantity = $item['quantity'] ?? 0;
            $updateNumIids[$item['num_iid']][$item['type']][] = $skuId . ':' . $quantity;
        }
        $requests = [];
        foreach ($updateNumIids as $numIid => $typeSkus) {
            foreach ($typeSkus as $type => $skus) {
                foreach (array_chunk($skus, 20) as $skuChunk) {
                    $skuIdQuantities = implode(";", $skuChunk);
                    $request = new SkusQuantityUpdateRequest();
                    $request->setNumIid($numIid);
                    if ($type) {
                        $request->setType($type);
                    }
                    $request->setSkuidQuantities($skuIdQuantities);
                    $requests[] = $request;
                }
            }
        }

        return $requests;
    }

    public function isSuccess($response)
    {
        $isSuccess = true;
        foreach ($response as $item) {
            $responseSuccess = data_get($item, 'skus_quantity_update_response.item.skus.sku', []);
            if (empty($responseSuccess)) {
                $isSuccess = false;
                break;
            }
        }

        return $isSuccess;
    }

    public function parseErrorCode($response)
    {
        $subMsg = $response['sub_msg'] ?? '';
        if (!empty($subMsg) && Str::contains($subMsg, [
            '库存中心服务正忙，请稍后再试',
            '数据存储服务正在维护中，请稍后再试！',
            '远程服务调用超时',
            '服务不可用',
            '数据存储服务正忙，请稍后再试',
            'This ban will last for 1 more seconds',
        ])) {
            // 失败重试
            return self::ERROR_CODE_RETRY;
        }

        return self::ERROR_CODE_FAIL;
    }

    public function mockProxy()
    {
        $requests = $this->getRequest();
        foreach ($requests as $request) {
            $request->getData();
        }
    }
}
