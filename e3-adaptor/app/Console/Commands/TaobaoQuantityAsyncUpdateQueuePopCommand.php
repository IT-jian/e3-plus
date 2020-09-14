<?php


namespace App\Console\Commands;


use App\Models\TaobaoSkusQuantityUpdateQueue;
use App\Services\Adaptor\Taobao\Jobs\SkuQuantityAsyncUpdateJob;
use Illuminate\Console\Command;

class TaobaoQuantityAsyncUpdateQueuePopCommand extends Command
{
    protected $name = 'adaptor:taobao:pop_sku_async_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将淘宝sku异步同步的队列组装报文之后同步平台';

    public function handle()
    {
        $minutes = 1;
        $this->info('start pop sku queue' . time());

        if (TaobaoSkusQuantityUpdateQueue::isLimitedByPlatform()) {
            $this->info('limited by platform');
        }
        $qps = 10;
        $delayStep = 1;
        $skipQueues = [];
        $skuUpdateQueues = $incrUpdateQueues = [];
        $total = TaobaoSkusQuantityUpdateQueue::where('status', 0)->count();
        if (empty($total)) {
            return true;
        }
        $this->info('total count ' . $total);
        $batchVersion = time();
        // 查询组织代码
        $queues = TaobaoSkusQuantityUpdateQueue::where('status', 0)->get();
        foreach ($queues->sortBy('sku_version') as $queue) {
            // 1. 如果 全量同步之前，未处理的全量同步队列，则直接覆盖
            if (1 == $queue['update_type'] && isset($skuUpdateQueues[$queue['sku_id']])) {
                $skipQueues[] = $skuUpdateQueues[$queue['sku_id']];
                unset($skuUpdateQueues[$queue['sku_id']]);
            } else if (1 == $queue['update_type'] && isset($incrUpdateQueues[$queue['sku_id']])) {
                // 2. 如果 全量同步之前，未处理的增量同步队列，则直接覆盖
                foreach ($incrUpdateQueues[$queue['sku_id']] as $incrUpdateQueue) {
                    $skipQueues[] = $incrUpdateQueue;
                }
                unset($incrUpdateQueues[$queue['sku_id']]);
            }

            if (1 == $queue['update_type']) {
                $skuUpdateQueues[$queue['sku_id']] = $queue;
            } else if (2 == $queue['update_type']) { // 同一个sku，会有多条记录，都记录着，job执行的时候进行汇总
                $incrUpdateQueues[$queue['sku_id']][] = $queue;
            }
        }
        // 不需要同步的处理为成功
        if ($skipQueues) {
            \DB::transaction(function () use ($skipQueues) {
                foreach (array_chunk(array_column($skipQueues, 'id'), 500) as $queueIds) {
                    TaobaoSkusQuantityUpdateQueue::whereIn('id', $queueIds)->update(['status' => 1, 'message' => 'rule skip']);
                }
            }, 3);
        }

        // 全量同步库存
        $shopItemSkuMap = $incrShopItemSkuMap = [];
        foreach ($skuUpdateQueues as $skuId => $queue) {
            $shopItemSkuMap[$queue['shop_code']][$queue['num_iid']][$skuId] = $queue;
        }
        // 增量同步库存
        foreach ($incrUpdateQueues as $skuId => $queues) {
            foreach ($queues as $queue) {
                $incrShopItemSkuMap[$queue['shop_code']][$queue['num_iid']][$queue['sku_id']][] = $queue;
            }
        }

        // 单个店铺最大弹出 item 数量
        $maxShopItems = $minutes * 60 * $qps;
        $shopDelayMap = [];
        $shopJobCountMap = [];
        $dispatchQueueParams = []; // 待分发的队列
        $queueIds = []; // 即将更新队列ID
        foreach ($shopItemSkuMap as $shopCode => $items) {
            foreach (array_chunk($items, $qps, true) as $chunkItems) {
                if (!isset($shopDelayMap[$shopCode])) {
                    $shopDelayMap[$shopCode] = 1;
                }
                if (!isset($shopJobCountMap[$shopCode])) {
                    $shopJobCountMap[$shopCode] = 0;
                }
                $jobDelaySeconds = $shopDelayMap[$shopCode];
                foreach ($chunkItems as $numIid => $chunkItem) {
                    // 一次只能请求20个sku
                    foreach (array_chunk($chunkItem, 20) as $chunkSkus) {
                        $params = [
                            'shop_code'     => $shopCode,
                            'num_iid'       => $numIid,
                            'skus'          => $chunkSkus,
                            'update_type'   => 1,
                            'batch_version' => $batchVersion,
                        ];
                        foreach ($chunkSkus as $queue) {
                            $queueIds[] = $queue['id'];
                        }
                        if (isset($incrShopItemSkuMap[$shopCode][$numIid])) {
                            $incrSkuParams = [];
                            foreach ($chunkSkus as $sku) {
                                if (isset($incrShopItemSkuMap[$shopCode][$numIid][$sku['sku_id']])) {
                                    $skus = $incrShopItemSkuMap[$shopCode][$numIid][$sku['sku_id']];
                                    foreach ($skus as $queue) {
                                        $queueIds[] = $queue['id'];
                                    }
                                    $incrSkuParams[] = $sku;
                                    // 已经处理的删除掉
                                    unset($incrShopItemSkuMap[$shopCode][$numIid][$sku['sku_id']]);
                                }
                            }
                            if ($incrSkuParams) { // 存在全量之后更新增量的请求，则需要过滤出来
                                $incrParams = $params;
                                $incrParams['update_type'] = 2;
                                $incrParams['skus'] = $incrSkuParams;
                                $dispatchQueueParams[] = [
                                    'job' => $params,
                                    'chain_job' => $params,
                                    'delay' => $jobDelaySeconds
                                ];
                            } else {
                                $dispatchQueueParams[] = [
                                    'job' => $params,
                                    'delay' => $jobDelaySeconds
                                ];
                            }
                        } else {
                            $dispatchQueueParams[] = [
                                'job' => $params,
                                'delay' => $jobDelaySeconds
                            ];
                        }

                        // 同步数量
                        $shopJobCountMap[$shopCode] += 1;
                        if ($shopJobCountMap[$shopCode] >= $maxShopItems) {
                            break 3;
                        }
                    }
                }
                $shopDelayMap[$shopCode] += $delayStep;
            }
        }

        // 处理增量
        foreach ($incrShopItemSkuMap as $shopCode => $items) {
            if (empty($items)) { // 本批次前面有批量同步
                continue;
            }

            if (!isset($shopJobCountMap[$shopCode])) {
                $shopJobCountMap[$shopCode] = 0;
            }

            if ($shopJobCountMap[$shopCode] >= $maxShopItems) {
                continue;
            }
            foreach (array_chunk($items, $qps, true) as $chunkItems) {
                if (!isset($shopDelayMap[$shopCode])) {
                    $shopDelayMap[$shopCode] = 1;
                }
                $jobDelaySeconds = $shopDelayMap[$shopCode];
                foreach ($chunkItems as $numIid => $chunkItem) {
                    // 一次只能请求20个sku
                    foreach (array_chunk($chunkItem, 20) as $chunkSkus) {
                        $params = [
                            'shop_code'     => $shopCode,
                            'num_iid'       => $numIid,
                            'skus'          => $chunkSkus,
                            'update_type'   => 2, // 类型 增量
                            'batch_version' => $batchVersion,
                        ];

                        foreach ($chunkSkus as $skus) {
                            foreach ($skus as $queue) {
                                $queueIds[] = $queue['id'];
                            }
                        }
                        $dispatchQueueParams[] = [
                            'job' => $params,
                            'delay' => $jobDelaySeconds
                        ];

                        // 同步数量
                        $shopJobCountMap[$shopCode] += 1;
                        if ($shopJobCountMap[$shopCode] >= $maxShopItems) {
                            break 3;
                        }
                    }
                }
                $shopDelayMap[$shopCode] += $delayStep;
            }
        }

        \DB::transaction(function () use ($queueIds, $batchVersion) {
            foreach (array_chunk($queueIds, 600) as $queueIds) {
                TaobaoSkusQuantityUpdateQueue::whereIn('id', $queueIds)->update(['status' => 2, 'batch_version' => $batchVersion]);
            }
        }, 3);

        // 开始组织推送
        foreach ($dispatchQueueParams as $queueParam) {
            if (isset($queueParam['chain_job'])) {
                dispatch((new SkuQuantityAsyncUpdateJob($queueParam['job']))
                             ->chain([
                                         new SkuQuantityAsyncUpdateJob($queueParam['chain_job'])
                                     ])
                             ->delay($queueParam['delay']));
            } else {
                dispatch((new SkuQuantityAsyncUpdateJob($queueParam['job']))->delay($queueParam['delay']));
            }
        }

        $this->info('end pop sku queue' . time());

        return true;
    }
}
