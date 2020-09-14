<?php

namespace App\Providers;

use App\Events\DatabaseQueryExceptionEvent;
use App\Listeners\DatabaseQueryExceptionReportListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        DatabaseQueryExceptionEvent::class                             => [ // 数据库异常监听
            DatabaseQueryExceptionReportListener::class,
        ],

        // 数据库 mode 转换
        /*'Illuminate\Database\Events\StatementPrepared' => [
            'App\Listeners\StatementPreparedListener',
        ]*/
    ];

    public function boot()
    {
        // 根据不同平台注册相应的事件
        $adaptorConfig = config('adaptor.adaptors.' . config('adaptor.default', 'taobao'), []);
        $platformEvents = data_get($adaptorConfig, 'events', []);
        $this->listen = array_merge($this->listen, $platformEvents);

        parent::boot();
    }
}
