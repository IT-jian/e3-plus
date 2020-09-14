<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Services\Adaptor\Taobao\Repository\TaobaoSkuQuantityUpdateQueueRepository;
use App\Services\HubApi\BaseApi;
use Carbon\Carbon;

/**
 * 10-库存更新 异步处理
 *
 * Class SkusQuantityUpdateHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/08/17 13:39
 */
class SkusQuantityAsyncUpdateHubApi extends BaseApi
{
    /**
     * @var TaobaoSkuQuantityUpdateQueueRepository
     */
    private $repository;

    /**
     * SkusQuantityAsyncUpdateHubApi constructor.
     * @param TaobaoSkuQuantityUpdateQueueRepository $repository
     */
    public function __construct(TaobaoSkuQuantityUpdateQueueRepository $repository)
    {
        $this->repository = $repository;
    }

    // 必填字段
    protected $notNullFields = ['data', 'shop_code'];

    public function proxy()
    {
        $queues = [];
        $timestamp = $this->data['timestamp'];
        $shopCode = $this->data['shop_code'];
        $current = Carbon::now()->toDateTimeString();
        foreach ($this->data['data'] as $sku) {
            $queues[] = [
                'num_iid'     => $sku['num_iid'],
                'sku_id'      => $sku['sku_id'],
                'shop_code'   => $shopCode,
                'quantity'    => $sku['quantity'],
                'outer_id'    => $sku['outer_id'] ?? '',
                'update_type' => $sku['type'],
                'sku_version' => $timestamp,
                'batch_version' => 0,
                'status' => 0,
                'created_at' => $current,
                'updated_at' => $current,
            ];
        }

        if ($queues) {
            $result = $this->repository->insertMulti($queues);
            if (!$result) {
                $this->fail();
            }
        }

        return $this->success();
    }
}
