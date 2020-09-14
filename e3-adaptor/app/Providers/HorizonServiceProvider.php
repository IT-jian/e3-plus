<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Horizon\HorizonServiceProvider as ServiceProvider;

/**
 * 重写provider的超时监听类
 *
 * Class HorizonServiceProvider
 * @package App\Providers
 *
 * @author linqihai
 * @since 2020/3/4 14:27
 */
class HorizonServiceProvider extends ServiceProvider
{
    protected function registerEvents()
    {
        $events = $this->app->make(Dispatcher::class);
        // 重置的超时监听类
        $this->events[\Laravel\Horizon\Events\LongWaitDetected::class] = [
            \App\Listeners\HorizonLongWaitSendNotification::class,
        ];
        foreach ($this->events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
        // 权限校验
        \Laravel\Horizon\Horizon::auth(function () {
            return \Auth::check();
        });
    }
}
