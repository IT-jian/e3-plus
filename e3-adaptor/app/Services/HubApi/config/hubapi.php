<?php

return [
    // 默认 hub api
    'default' => 'adidas',
    'customers' => [
        'adidas' => [
            'app_key' => ''
        ],
    ],
    'sku_update_api_batch' => env('HUB_API_SKU_UPDATE_BATCH', 0),
];
