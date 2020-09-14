<?php


namespace App\Services\HubApi;


use Illuminate\Support\ServiceProvider;

class HubApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'hubapi');
        $this->app->singleton('hubapi', function ($app) {
            return new HubApiManager($app);
        });
    }

    public function provides()
    {
        return ['hubapi'];
    }

    protected function configPath()
    {
        return __DIR__ . '/config/hubapi.php';
    }
}
