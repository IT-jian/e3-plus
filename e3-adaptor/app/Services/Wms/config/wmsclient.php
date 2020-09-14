<?php

return [
    // 默认 wms 请求客户端
    'default' => 'adidas', // 默认 wms 客户端类型
    'stop_push' => env('ADIDAS_WMS_STOP_PUSH', '1'), // 默认 wms 客户端类型
    'adidas'  => [
        'shunfeng' => [
            'url'           => env('ADIDAS_SHUNFENG_URL', ''), // 请求地址
            'system_key'    => env('ADIDAS_SHUNFENG_SYSTEM_KEY', ''),
            'system_secret' => env('ADIDAS_SHUNFENG_SYSTEM_SECRET', ' '),
            'contact_code'  => env('ADIDAS_SHUNFENG_CONTACT_CODE', ''),
            'simulation'    => env('ADIDAS_SHUNFENG_SIMULATION', '0'), // 是否模拟请求
            'timeout'       => env('ADIDAS_SHUNFENG_TIMEOUT', '5'), // http 请求超时(秒)
        ],
        'baozun'   => [
            'url'           => env('ADIDAS_BAOZUN_URL', ''), // 请求地址
            'system_key'    => env('ADIDAS_BAOZUN_SYSTEM_KEY', ''),
            'system_secret' => env('ADIDAS_BAOZUN_SYSTEM_SECRET', ' '),
            'contact_code'  => env('ADIDAS_BAOZUN_CONTACT_CODE', ''),
            'simulation'    => env('ADIDAS_BAOZUN_SIMULATION', '0'), // 是否模拟请求
            'timeout'       => env('ADIDAS_BAOZUN_TIMEOUT', '5'), // http 请求超时(秒)
        ],
    ],
];
