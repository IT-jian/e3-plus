<?php

namespace App\Providers;

use App\Services\Wms\WmsClientServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            // $this->app->configure('generator');
            // $this->app->register(\PlatformAdaptor\Generator\GeneratorsServiceProvider::class);
        }

        # cache 缓存
        $this->app->register(\Illuminate\Redis\RedisServiceProvider::class);

        // adaptor
        $this->app->register(\App\Services\Adaptor\AdaptorServiceProvider::class);
        // hub 客户端，发起处理请求
        $this->app->register(\App\Services\Hub\HubClientServiceProvider::class);
        // hub 服务端，接受请求
        $this->app->register(\App\Services\HubApi\HubApiServiceProvider::class);
        // wms 客户端，发起处理请求
        $this->app->register(WmsClientServiceProvider::class);

    }

    public function boot()
    {
        \Dusterio\LumenPassport\LumenPassport::routes($this->app);
    }
}
