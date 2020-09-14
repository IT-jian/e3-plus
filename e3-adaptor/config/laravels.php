<?php
/**
 * @see https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md  Chinese
 * @see https://github.com/hhxsv5/laravel-s/blob/master/Settings.md  English
 */
// 处理 计划任务配置
$defaultTimers = [
    // Enable LaravelScheduleJob to run `php artisan schedule:run` every 1 minute, replace Linux Crontab
    \App\Jobs\Timer\SysStdPushQueueBatchTimer::class, // 触发定时推送队列
    \App\Jobs\Timer\SysStdPushQueueFailTimer::class, // 触发失败更新，重新入列
];
if ('swoole' == config('app.crontab_driver')) { // 根据配置来决定 crontab 驱动
    $defaultTimers[] = \Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob::class;
}
$adaptorConfig = config('adaptor.adaptors.' . config('adaptor.default', 'taobao'), []);
$timer = data_get($adaptorConfig, 'timer', []);
if ($timer) {
    $defaultTimers = array_merge($defaultTimers, $timer);
}
return [
    'listen_ip'                => env('LARAVELS_LISTEN_IP', '127.0.0.1'),
    'listen_port'              => env('LARAVELS_LISTEN_PORT', 5200),
    'socket_type'              => defined('SWOOLE_SOCK_TCP') ? SWOOLE_SOCK_TCP : 1,
    'enable_coroutine_runtime' => false,
    'server'                   => env('LARAVELS_SERVER', 'LaravelS'), // 当通过LaravelS响应数据时，设置HTTP头部Server的值
    'handle_static'            => env('LARAVELS_HANDLE_STATIC', false),
    'laravel_base_path'        => env('LARAVEL_BASE_PATH', base_path()),
    'inotify_reload'           => [
        'enable'        => env('LARAVELS_INOTIFY_RELOAD', false),
        'watch_path'    => base_path(),
        'file_types'    => ['.php'],
        'excluded_dirs' => [],
        'log'           => true,
    ],
    'event_handlers'           => [],
    'websocket'                => [
        'enable' => false,
        //'handler' => XxxWebSocketHandler::class,
    ],
    'sockets'                  => [],
    'processes'                => [
        //[
        //    'class'    => \App\Processes\TestProcess::class,
        //    'redirect' => false, // Whether redirect stdin/stdout, true or false
        //    'pipe'     => 0 // The type of pipeline, 0: no pipeline 1: SOCK_STREAM 2: SOCK_DGRAM
        //    'enable'   => true // Whether to enable, default true
        //],
    ],
    'timer'                    => [ // 定时器，精确到毫秒
        'enable'        => env('LARAVELS_TIMER', true),
        'jobs'          => $defaultTimers,
        'max_wait_time' => 5,
    ],
    'events'                   => [],
    'swoole_tables'            => [],
    'register_providers'       => [
        Barryvdh\Cors\ServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        // PlatformAdaptor\Generator\GeneratorsServiceProvider::class
    ],
    'cleaners'                 => [
        // If you use the session/authentication/passport in your project
        Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
        Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,

        // If you use the package "tymon/jwt-auth" in your project
        // Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
        // Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
        // Hhxsv5\LaravelS\Illuminate\Cleaners\JWTCleaner::class,

        // If you use the package "spatie/laravel-menu" in your project
        // Hhxsv5\LaravelS\Illuminate\Cleaners\MenuCleaner::class,
        // ...
    ],
    'destroy_controllers'      => [ // 销毁全部控制器。避免在构造函数中初始化请求级的数据，应在具体Action中读取，这样编码风格更合理
        'enable'        => false,
        'excluded_list' => [
            //\App\Http\Controllers\TestController::class,
        ],
    ],
    'swoole'                   => [
        'daemonize'          => env('LARAVELS_DAEMONIZE', false),
        'dispatch_mode'      => 2,
        'reactor_num'        => function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 4,
        'worker_num'         => function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 8,
        'task_worker_num'    => function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 8,
        'task_ipc_mode'      => 1,
        'task_max_request'   => 8000,
        'task_tmpdir'        => @is_writable('/dev/shm/') ? '/dev/shm' : '/tmp',
        'max_request'        => 8000,
        'open_tcp_nodelay'   => true,
        'pid_file'           => storage_path('laravels.pid'),
        'log_file'           => storage_path(sprintf('logs/swoole-%s.log', date('Y-m'))),
        'log_level'          => 4,
        'document_root'      => base_path('public'),
        'buffer_output_size' => 2 * 1024 * 1024,
        'socket_buffer_size' => 128 * 1024 * 1024,
        'package_max_length' => 4 * 1024 * 1024,
        'reload_async'       => true,
        'max_wait_time'      => 60,
        'enable_reuse_port'  => true,
        'enable_coroutine'   => false,
        'http_compression'   => false,

        // Slow log
        // 'request_slowlog_timeout' => 2,
        // 'request_slowlog_file'    => storage_path(sprintf('logs/slow-%s.log', date('Y-m'))),
        // 'trace_event_worker'      => true,

        /**
         * More settings of Swoole
         * @see https://wiki.swoole.com/wiki/page/274.html  Chinese
         * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration  English
         */
    ],
];
