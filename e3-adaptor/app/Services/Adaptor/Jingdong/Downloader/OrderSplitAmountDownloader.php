<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Api\OrderSplitAmount;
use App\Services\Adaptor\Jingdong\Repository\JingdongOrderSplitAmountRepository;
use Illuminate\Support\Carbon;

/**
 * 订单商品明细行金额分摊明细
 *
 * Class OrderSplitAmountDownloader
 * @package App\Services\Adaptor\Jingdong\Downloader
 */
class OrderSplitAmountDownloader implements DownloaderContract
{
    private $repository;

    public function __construct(JingdongOrderSplitAmountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function download($params)
    {
        $shop = Shop::getShopByCode($params['shop_code']);
        $api = new OrderSplitAmount($shop);
        $systemInfo = config('adaptor.jingdong_system_info');
        if (empty($systemInfo['key'])) {
            throw new \Exception('未配置京东 systemKey 信息，请配置环境变量 ADAPTOR_JD_SYSTEM_KEY, ADAPTOR_JD_SYSTEM_NAME');
        }
        $sysKey = $systemInfo['key'];
        $sysName = $systemInfo['name'];

        if (isset($params['order_id']) && is_array($params['order_id'])) {
            $params['order_ids'] = $params['order_id'];
            unset($params['order_id']);
        }
        // 支持批量查询
        if (isset($params['order_ids']) && is_array($params['order_ids'])) {
            $failOrderIds = [];
            $orderSplitAmounts = $api->findMulti($params['order_ids'], $sysKey, $sysName);
            $insertData = [];
            foreach ($orderSplitAmounts as $orderId => $orderSplitAmount) {
                if ('SUCCESS' == $orderSplitAmount['code']) {
                    $insertData[] = $this->format($orderId, $shop['seller_nick'], $orderSplitAmount['data'] ?? []);
                } else {
                    $failOrderIds[] = $orderId;
                }
            }
            if ($insertData) {
                $this->repository->insertMulti($insertData, ['origin_content', 'updated_at']);
            }
            if (!empty($failOrderIds)) {
                throw new \Exception('查询京东优惠部分失败');
            }
        }
        if (isset($params['order_id']) && $params['order_id']) {
            $orderSplitAmount = $api->find($params['order_id'], $sysKey, $sysName);
            if ('SUCCESS' != $orderSplitAmount['code']) {
                throw new \Exception('查询京东优惠失败', $orderSplitAmount);
            }

            return $this->saveOrderSplitAmount($params['order_id'], $shop['seller_nick'], $orderSplitAmount['data'] ?? []);
        }

        return true;
    }

    public function saveOrderSplitAmount($orderId, $venderId, $content)
    {
        $formatData = [
            'order_id'        => $orderId,
            'vender_id'       => $venderId,
            'origin_content'  => json_encode($content),
            'origin_created'  => Carbon::now()->timestamp,
            'created_at'  => Carbon::now()->toDateTimeString(),
            'updated_at'  => Carbon::now()->toDateTimeString(),
        ];
        $row = $this->repository->getRow(['order_id' => $orderId]);
        if (!empty($row)) {
            $this->repository->update(['origin_content' => json_encode($content)], ['order_id' => $orderId]);
        } else {
            $this->repository->insert($formatData);
        }

        return true;
    }

    public function format($orderId, $venderId, $content)
    {
        $currentOrderId = data_get($content, '0.orderId', '');
        if ($currentOrderId != $orderId) {
            \Log::error('order_id not equal split amount', [$content]);
            throw new \Exception('error split amount not found');
        }
        if ($currentOrderId) {
            $orderId = $currentOrderId;
        }

        return [
            'order_id'        => $orderId,
            'vender_id'       => $venderId,
            'origin_content'  => json_encode($content),
            'origin_created'  => Carbon::now()->timestamp,
            'created_at'  => Carbon::now()->toDateTimeString(),
            'updated_at'  => Carbon::now()->toDateTimeString(),
        ];
    }
}
