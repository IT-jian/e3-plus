<?php

return [
    // 默认 hub 请求客户端
    'default' => 'adidas', // 默认 hub 客户端类型
    'clients' => [
        'adidas' => [
            'url'        => env('ADIDAS_HUB_URL', 'http://39.100.34.48/adaptor/simulation'), // 请求地址
            'app_id'     => env('ADIDAS_HUB_APP_ID', 'h7g96zrszps7bbb3bqchr4wb'),
            'app_auth'   => 'APPCODE ' . env('ADIDAS_HUB_APP_AUTH', 'APPCODE '),
            'app_key'    => env('ADIDAS_HUB_APP_KEY', '3'),
            'app_secret' => env('ADIDAS_HUB_APP_SECRET', 'lbn3IlL04SkuS3fhGYtaQ8ItlHSsGMcPusBcoi0m'),
            'app_env'    => env('ADIDAS_HUB_APP_ENV', 'tmall'),
            'simulation' => env('ADIDAS_HUB_SIMULATION', '1'), // 是否模拟请求
            'timeout'    => env('ADIDAS_HUB_TIMEOUT', '5'), // http 请求超时(秒)
            'stop_push'  => env('ADIDAS_HUB_PUSH_STOP', '1'), // 停止推送请求
        ],
        'qimen' => [
            'best_url' => env('QIMEN_BEST_URL'), // 指定奇门地址
            'gateway_url' => env('QIMEN_GATEWAY_URL', 'http://qimen.api.taobao.com/router/qm'), // 地址
            'target_app_key' => env('QIMEN_TARGET_APP_KEY'), // target_app_key
            'customerid' => env('QIMEN_CUSTOMER_ID'), // target_app_key
        ],
        'hufu' => [
            'url'        => env('HUFU_URL'), // 请求地址
            'app_key'    => env('ADIDAS_HUB_APP_KEY', '3'),
            'app_secret' => env('ADIDAS_HUB_APP_SECRET', 'lbn3IlL04SkuS3fhGYtaQ8ItlHSsGMcPusBcoi0m'),
            'simulation' => env('ADIDAS_HUB_SIMULATION', '1'), // 是否模拟请求
            'timeout'    => env('ADIDAS_HUB_TIMEOUT', '5'), // http 请求超时(秒)
        ],
    ],
    // 1：默认，先入队列，要推送时再组装报文
    // 2：入队列时组装好报文，推送时判断当前版本号，判断是否重新组装，否则直接推送
    'push_queue_format_type' => env('ADAPTOR_PUSH_QUEUE_FORMAT_TYPE', 1),
    'cutover_date' => env('ADAPTOR_CUTOVER_DATE', ''),
    'aisino' => [
        'url' => env('ADIDAS_AISINO_URL', ''),
        'consumer_code' => env('ADIDAS_AISINO_CONSUMER_CODE', 'Adidas_Test'), // key
        'sign_pwd' => env('ADIDAS_AISINO_SIGN_PWD', '8a11f5466758bffc016758bffca80000'), // secret
    ]
];
