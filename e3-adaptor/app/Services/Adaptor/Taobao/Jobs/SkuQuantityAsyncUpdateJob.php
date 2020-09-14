<?php


namespace App\Services\Adaptor\Taobao\Jobs;


use App\Facades\TopClient;
use App\Jobs\Job;
use App\Models\SkuInventoryPlatformLog;
use App\Models\TaobaoSkusQuantityUpdateQueue;
use App\Services\Adaptor\Taobao\Listeners\TaobaoPushQueueTrait;
use App\Services\Adaptor\Taobao\Repository\TaobaoSkuQuantityUpdateQueueRepository;
use App\Services\Hub\HubRequestEnum;
use App\Services\Platform\Exceptions\PlatformServerSideException;
use App\Services\Platform\Taobao\Client\Top\Request\SkusQuantityUpdateRequest;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SkuQuantityAsyncUpdateJob extends Job
{
    use TaobaoPushQueueTrait;

    public $queue = 'taobao_sku_async_update';

    // public $delay = 10;

    // public $tries = 5;

    // public $timeout = 10 单个处理超时时间

    private $params;

    const STATUS_INIT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_LOCK = 2;
    const STATUS_FAIL_RETRY = 3; // 失败重试
    const STATUS_FAIL_BREAK = 4; // 失败不重试
    const STATUS_FAIL_UNKNOWN = 5; // 未知失败，不重试

    const MAX_TRY_TIME = 3;

    /**
     * SkuQuantityAsyncUpdateJob constructor.
     * @param array $params 请求参数结构
     * {
     *    "shop_code": "EMC1",
     *    "num_iid": "商品ID",
     *    "skus": [
     *      {
     *        "id": "queue id",
     *        "quantity": "更新数量",
     *        "sku_id": "sku_id",
     *        "sku_version": "更新版本"
     *      }
     *    ],
     *    "update_type": "增量还是全量",
     *    "batch_version": "批量版本"
     * }
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        $skuQuantityParams = $queueIds = [];
        // 1. 组织参数
        $numIid = $this->params['num_iid'];
        $now = Carbon::now();
        $skus = $this->params['skus'];
        $shopCode = $this->params['shop_code'];
        $batchVersion = $this->params['batch_version'];
        $type = $this->params['update_type'];
        $simulateResponse = [];
        $tryTimes = 0;
        if (2 == $type) {
            // 增量结构为多个队列
            foreach ($skus as $queues) {
                $quantity = 0;
                foreach ($queues as $queue) {
                    $quantity += $queue['quantity'] ?? 0;
                    $queueIds[] = $queue['id'];
                    $tryTimes = max($tryTimes, $queue['try_times']);
                }
                $queue = current($queues);
                $skuId = $queue['sku_id'];
                $skuQuantityParams[] = $skuId . ':' . $quantity;

                $simulateResponse[] = [
                    'modified' => Carbon::now()->toDateTimeString(),
                    'quantity' => $quantity,
                    'sku_id' => $skuId,
                ];
            }
        } else {
            foreach ($skus as $queue) {
                $skuId = $queue['sku_id'];
                $quantity = $queue['quantity'] ?? 0;
                $skuQuantityParams[] = $skuId . ':' . $quantity;
                $queueIds[] = $queue['id'];
                $tryTimes = max($tryTimes, $queue['try_times']);

                $simulateResponse[] = [
                    'modified' => Carbon::now()->toDateTimeString(),
                    'quantity' => $quantity,
                    'sku_id' => $skuId,
                ];
            }
        }
        $repository = new TaobaoSkuQuantityUpdateQueueRepository();
        if (TaobaoSkusQuantityUpdateQueue::isLimitedByPlatform()) {
            return $repository->updateStatus($queueIds, 0, 'limit by platform take a break');
        }

        $skuIdQuantities = implode(";", $skuQuantityParams);
        $request = new SkusQuantityUpdateRequest();
        $request->setNumIid($numIid);
        $request->setType($type);
        $request->setSkuidQuantities($skuIdQuantities);
        // 2. 请求平台
        $message = '';
        $response = [];
        $startAt = Carbon::now()->toDateTimeString();
        $status = self::STATUS_INIT;
        try {
            if ('production' == app()->environment()) {
                $response = TopClient::shop($shopCode)->execute($request);
            } else {
                usleep(200);
                data_set($response, 'skus_quantity_update_response.item.skus.sku', $simulateResponse);
            }
            if (data_get($response, 'skus_quantity_update_response.item.skus.sku', [])) {
                $status = self::STATUS_SUCCESS;
            } else {
                throw (new PlatformServerSideException())->setResponseBody($response);
            }
        } catch (\Exception $e) {
            if ($e instanceof PlatformServerSideException) {
                $response = $e->getResponseBody();
                $message = $response['sub_msg'] ?? '';
                $subCode = $response['sub_code'] ?? '';
                if (in_array($message, ['服务不可用'])) { // 被平台限流了，则标识
                    TaobaoSkusQuantityUpdateQueue::setLimitedByPlatform();
                }
                // 部分报错不再执行
                if ($message) {
                    if (Str::contains($message, [
                        '库存中心服务正忙，请稍后再试, 编辑库存数不能低于预扣库存数！',
                        '获取单个商品异常!',
                        '没有找到SKUID',
                        '该商品已被删除'
                    ])) {
                        // 失败不重试
                        $status = self::STATUS_FAIL_BREAK;
                    } else if (Str::contains($message, [
                        '库存中心服务正忙，请稍后再试',
                        '数据存储服务正在维护中，请稍后再试！',
                        '远程服务调用超时',
                        '服务不可用',
                        '数据存储服务正忙，请稍后再试',
                        'This ban will last for 1 more seconds',
                    ])) {
                        // 失败重试
                        $status = self::STATUS_INIT;
                    } else if ('isv.item-quantity-sku-update-service-error-tmall' == $subCode && Str::startsWith($message, '该商品为#')) { // 活动商品
                        // 失败不重试
                        $status = self::STATUS_FAIL_BREAK;
                    } else { // 其他失败
                        $status = self::STATUS_FAIL_UNKNOWN;
                    }
                }
            }
        }
        // 从 0 递增
        $tryTimes = $tryTimes + 1;
        if ($status == self::STATUS_INIT && $tryTimes >= self::MAX_TRY_TIME) { // 超过重试次数，则置为失败
            $status = self::STATUS_FAIL_RETRY; // 重试失败
        }
        $endAt = Carbon::now()->toDateTimeString();
        // 3. 记录结果
        $result = $repository->updateStatus($queueIds, $status, $message);
        if (false === $result) {
            // @todo 错误处理
        }
        // 4. 记录日志
        $logSkus = [];
        if (2 == $type) {
            foreach ($skus as $queues) {
                foreach ($queues as $index => $queue) {
                    $logSkus[] = $queue;
                }
            }
        } else {
            $logSkus = $skus;
        }
        $noticeContent = data_get($response, 'skus_quantity_update_response.item.skus.sku', []);
        if (in_array($status, [self::STATUS_FAIL_RETRY, self::STATUS_FAIL_BREAK, self::STATUS_FAIL_UNKNOWN])) {
            $noticeContent = $simulateResponse;
        }
        $log = [
            'num_iid' => $numIid,
            'shop_code' => $shopCode,
            'batch_version' => $batchVersion,
            'skus' => json_encode($logSkus),
            'update_type' => $type,
            'request' => json_encode($request->getData()),
            'response' => json_encode($response),
            'notice_content' => json_encode($noticeContent),
            'response_status' => $status,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ];

        $id =  SkuInventoryPlatformLog::insertGetId($log);
        if (false === $id) {
            \Log::error('insert sku inventory log fail', [$log]);
        }

        // 还在重试的，不做异步通知
        if (in_array($status, [self::STATUS_SUCCESS, self::STATUS_FAIL_RETRY, self::STATUS_FAIL_BREAK, self::STATUS_FAIL_UNKNOWN])) {
            // 插入异步通知队列
            $queue = $this->formatQueue($id, HubRequestEnum::SKU_INVENTORY_UPDATE_ACK);
            $this->pushQueue($queue);
        }

        return $result;
    }

    public function shouldTakeBreak()
    {
        Cache::get('key', 'default');
    }

    /**
     * 是否被平台限制了
     *
     * @param $response
     * @return bool
     */
    public function isLimitByPlatform($response)
    {
        // 1. 被限流了{"msg": "App Call Limited", "code": 7, "sub_msg": "This ban will last for 1 more seconds", "sub_code": "accesscontrol.limited-by-api-access-count", "request_id": "5o5smyfbvvpu"}
        // 2. 服务不可用 {"msg": "Remote service error", "code": 15, "sub_msg": "服务不可用", "sub_code": "isp.service-unavailable-tmall", "request_id": "nfe4bwf4h9hb"}
        if (isset($response['sub_msg']) && in_array($response['sub_msg'], ['服务不可用'])) { // isp.service-unavailable-tmall
            return true;
        }

        return false;
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['taobao_sku_async_update'];
    }
}
