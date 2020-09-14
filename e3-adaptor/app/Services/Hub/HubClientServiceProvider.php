<?php


namespace App\Services\Hub;


use Illuminate\Support\ServiceProvider;

/**
 * baison hub 请求客户端
 * app('hubclient')->hub('adidas')->tradeCreate();
 * 方法参照 InvoiceClientContract
 *
 * Class HubClientServiceProvider
 * @package App\Services\Hub
 *
 * @author linqihai
 * @since 2019/12/26 15:13
 */
class HubClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'hubclient');
        $this->app->singleton('hubclient', function ($app) {
            return new HubClientManager($app);
        });
    }

    public function provides()
    {
        return ['hubclient'];
    }

    protected function configPath()
    {
        return __DIR__ . '/config/hubclient.php';
    }
}
