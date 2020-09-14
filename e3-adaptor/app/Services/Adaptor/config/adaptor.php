<?php

return [
    'default'  => env('ADAPTOR_PLATFORM', 'taobao'),
    'adaptors' => [
        'taobao' => [
            'rds' => [
                'connection' => 'taobao_rds', // 对应 database.php 中的连接
                'trade_table' => 'jdp_tb_trade',
                'refund_table' => 'jdp_tb_refund',
                'item_table' => 'jdp_tb_item',
            ],
            'timer' => [ // 计划任务
                \App\Jobs\Timer\PopTaobao\RdsTradeBatchTimer::class, // 淘宝订单rds批量下载触发器
                \App\Jobs\Timer\PopTaobao\RdsRefundBatchTimer::class, // 淘宝退单rds批量下载触发器
                \App\Jobs\Timer\PopTaobao\ExchangeBatchTimer::class, // 淘宝换货单下载触发器
                \App\Jobs\Timer\PopTaobao\TradeCommentBatchTimer::class, // 淘宝订单评论下载
                \App\Jobs\Timer\PopTaobao\RdsItemBatchTimer::class, // 淘宝Rds平台商品下载
                \App\Jobs\Timer\PopTaobao\InvoiceApplyNoticeTimer::class, // 发票申请结果监听
            ],
            'events' => [ // 监听事件注册
                'App\Services\Adaptor\Taobao\Events\TaobaoTradeUpdateEvent'    => [
                    'App\Services\Adaptor\Taobao\Listeners\TaobaoTradeUpdateListener',
                ],
                'App\Services\Adaptor\Taobao\Events\TaobaoTradeCreateEvent'    => [
                    'App\Services\Adaptor\Taobao\Listeners\TaobaoTradeCreateListener',
                ],
                'App\Services\Adaptor\Taobao\Events\TaobaoTradeBatchCreateEvent'    => [
                    'App\Services\Adaptor\Taobao\Listeners\TaobaoTradeBatchCreateListener',
                ],
                'App\Services\Adaptor\Taobao\Events\TaobaoRefundUpdateEvent'   => [
                    'App\Services\Adaptor\Taobao\Listeners\TaobaoRefundUpdateListener',
                ],
                'App\Services\Adaptor\Taobao\Events\TaobaoRefundCreateEvent'   => [
                    'App\Services\Adaptor\Taobao\Listeners\TaobaoRefundCreateListener',
                ],
                'App\Services\Adaptor\Taobao\Events\TaobaoExchangeUpdateEvent' => [
                    'App\Services\Adaptor\Taobao\Listeners\TaobaoExchangeUpdateListener',
                ],
                'App\Services\Adaptor\Taobao\Events\TaobaoExchangeCreateEvent' => [
                    'App\Services\Adaptor\Taobao\Listeners\TaobaoExchangeCreateListener',
                ],
            ],
            'horizon_environments' => [ // horizon  配置
                'production' => [
                    'supervisor-1' => [
                        'connection' => 'redis',
                        'queue'      => ['default'],
                        'balance'    => 'auto',
                        'processes'  => 20,
                        'tries'      => 3,
                        'delay'      => 10,
                    ],
                    'supervisor-2' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'sys_std_push_hub', 'taobao_download',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 20,
                        'tries'        => 3,
                        'delay'        => 10,
                    ],
                    'supervisor-3' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'taobao_download', 'taobao_transfer', 'sys_std_push_hub',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 20,
                        'tries'        => 3,
                        'delay'        => 10,
                    ],
                    'supervisor-4' => [ // 天猫异步库存同步队列
                        'connection'   => 'redis',
                        'queue'        => [
                            'taobao_sku_async_update',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 5, // 目前生产有两台
                        'tries'        => 3,
                        'delay'        => 60,
                    ],
                    'supervisor-5' => [ // 天猫异步库存同步结果异步通知
                        'connection'   => 'redis',
                        'queue'        => [
                            'sku_inventory_update_ack_queue',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 5,
                        'tries'        => 3,
                        'delay'        => 60,
                    ],
                ],
                'testing' => [
                    'supervisor-1' => [
                        'connection' => 'redis',
                        'queue'      => ['default'],
                        'balance'    => 'auto',
                        'processes'  => 3,
                        'tries'      => 3,
                        'delay'      => 10,
                    ],
                    'supervisor-2' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'sys_std_push_hub', 'taobao_download',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 10,
                        'tries'        => 3,
                        'delay'        => 10,
                    ],
                    'supervisor-3' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'taobao_download', 'taobao_transfer', 'sys_std_push_hub',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 10,
                        'tries'        => 3,
                        'delay'        => 10,
                    ],
                    'supervisor-4' => [ // 天猫异步库存同步队列
                        'connection'   => 'redis',
                        'queue'        => [
                            'taobao_sku_async_update',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 10,
                        'tries'        => 3,
                        'delay'        => 60,
                    ],
                    'supervisor-5' => [ // 天猫异步库存同步结果异步通知
                        'connection'   => 'redis',
                        'queue'        => [
                            'sku_inventory_update_ack_queue',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 10,
                        'tries'        => 3,
                        'delay'        => 60,
                    ],
                ],
                'local' => [
                    'supervisor-1' => [
                        'connection' => 'redis',
                        'queue'      => ['default'],
                        'balance'    => 'auto',
                        'processes'  => 2,
                        'tries'      => 3,
                        'delay'      => 10,
                    ],
                    'supervisor-2' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'sys_std_push_hub', 'taobao_download',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 5,
                        'tries'        => 5,
                        'delay'        => 10,
                    ],
                    'supervisor-3' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'taobao_download', 'taobao_transfer', 'sys_std_push_hub',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 5,
                        'tries'        => 5,
                        'delay'        => 10, // 失败重试间隔
                    ],
                    'supervisor-4' => [ // 天猫异步库存同步队列
                        'connection'   => 'redis',
                        'queue'        => [
                            'taobao_sku_async_update',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 5,
                        'tries'        => 3,
                        'delay'        => 60,
                    ],
                    'supervisor-5' => [ // 天猫异步库存同步结果异步通知
                        'connection'   => 'redis',
                        'queue'        => [
                            'sku_inventory_update_ack_queue',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 5,
                        'tries'        => 3,
                        'delay'        => 60,
                    ],
                ],
            ]
        ],
        'jingdong' => [
            'rds' => [
                'connection' => 'jingdong_rds', // 对应 database.php 中的连接
                'trade_table' => 'yd_pop_order',
            ],
            'timer' => [
                \App\Jobs\Timer\PopJingdong\RdsTradeBatchTimer::class, // 订单
                // \App\Jobs\Timer\PopJingdong\StepTradeBatchTimer::class, // 预售单
                \App\Jobs\Timer\PopJingdong\RefundBatchTimer::class, // 退单下载
                \App\Jobs\Timer\PopJingdong\RefundApplyBatchTimer::class, // 退款申请下载
                \App\Jobs\Timer\PopJingdong\RefundApplyCheckBatchTimer::class, // 退款申请更新下载
                \App\Jobs\Timer\PopJingdong\RefundUpdateBatchTimer::class, // 退单更新
                \App\Jobs\Timer\PopJingdong\RefundFreightUpdateBatchTimer::class, // 运单更新
                \App\Jobs\Timer\PopJingdong\VenderCommentsBatchTimer::class, // 评论下载
                \App\Jobs\Timer\PopJingdong\ItemBatchTimer::class, // 平台商品下载
            ],
            'events' => [
                'App\Services\Adaptor\Jingdong\Events\JingdongTradeUpdateEvent'    => [
                    'App\Services\Adaptor\Jingdong\Listeners\JingdongTradeUpdateListener',
                ],
                'App\Services\Adaptor\Jingdong\Events\JingdongTradeCreateEvent'    => [
                    'App\Services\Adaptor\Jingdong\Listeners\JingdongTradeCreateListener',
                ],
                'App\Services\Adaptor\Jingdong\Events\JingdongTradeBatchCreateEvent'    => [
                    'App\Services\Adaptor\Jingdong\Listeners\JingdongTradeBatchCreateListener',
                ],
                'App\Services\Adaptor\Jingdong\Events\JingdongRefundCreateEvent'    => [
                    'App\Services\Adaptor\Jingdong\Listeners\JingdongRefundCreateListener',
                ],
                'App\Services\Adaptor\Jingdong\Events\JingdongRefundUpdateEvent'    => [
                    'App\Services\Adaptor\Jingdong\Listeners\JingdongRefundUpdateListener',
                ],
                'App\Services\Adaptor\Jingdong\Events\JingdongExchangeCreateEvent'    => [
                    'App\Services\Adaptor\Jingdong\Listeners\JingdongExchangeCreateListener',
                ],
                'App\Services\Adaptor\Jingdong\Events\JingdongExchangeUpdateEvent'    => [
                    'App\Services\Adaptor\Jingdong\Listeners\JingdongExchangeUpdateListener',
                ],
            ],
            'horizon_environments' => [
                'production' => [
                    'supervisor-1' => [
                        'connection' => 'redis',
                        'queue'      => ['default', 'jingdong_download', 'sys_std_push_hub'],
                        'balance'    => 'auto',
                        'processes'  => 20,
                        'tries'      => 3,
                        'delay'      => 10,
                    ],
                    'supervisor-2' => [
                        'connection'   => 'redis',
                        'queue'        => [// 京东推送之前，要先拉取金额明细
                            'order_split_amount_download_job', 'sys_std_push_hub', 'jingdong_download',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 30,
                        'tries'        => 3,
                        'delay'        => 10,
                    ],
                    'supervisor-3' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'jingdong_download', 'jingdong_transfer', 'sys_std_push_hub',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 30,
                        'tries'        => 3,
                        'delay'        => 10,
                    ],
                ],
                'testing' => [
                    'supervisor-1' => [
                        'connection' => 'redis',
                        'queue'      => ['default', 'jingdong_download', 'sys_std_push_hub'],
                        'balance'    => 'auto',
                        'processes'  => 3,
                        'tries'      => 3,
                        'delay'      => 10,
                    ],
                    'supervisor-2' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'order_split_amount_download_job', 'sys_std_push_hub', 'jingdong_download',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 15,
                        'tries'        => 3,
                        'delay'        => 10,
                    ],
                    'supervisor-3' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'jingdong_download', 'jingdong_transfer', 'sys_std_push_hub',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 15,
                        'tries'        => 3,
                        'delay'        => 10,
                    ],
                ],
                'local' => [
                    'supervisor-1' => [
                        'connection' => 'redis',
                        'queue'      => ['default', 'jingdong_download', 'sys_std_push_hub'],
                        'balance'    => 'auto',
                        'processes'  => 2,
                        'tries'      => 3,
                        'delay'      => 10,
                    ],
                    'supervisor-2' => [
                        'connection'   => 'redis',
                        'queue'        => [// 京东推送之前，要先拉取金额明细
                           'order_split_amount_download_job', 'sys_std_push_hub', 'jingdong_download',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 5,
                        'tries'        => 5,
                        'delay'        => 10,
                    ],
                    'supervisor-3' => [
                        'connection'   => 'redis',
                        'queue'        => [
                            'jingdong_download', 'jingdong_transfer', 'sys_std_push_hub',
                        ],
                        'balance'      => 'auto',
                        'minProcesses' => 0,
                        'processes'    => 5,
                        'tries'        => 5,
                        'delay'        => 10, // 失败重试间隔
                    ],
                ],
            ],
        ]
    ],
    'jingdong_system_info' => [
        'key' => env('ADAPTOR_JD_SYSTEM_KEY', ''),
        'name' => env('ADAPTOR_JD_SYSTEM_NAME', ''),
    ]
];
