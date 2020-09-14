<?php


namespace App\Services\Wms;


use Illuminate\Support\ServiceProvider;

/**
 * 请求WMS客户端
 * app('wmsclient')->wms('shunfeng')->tradeCancelSuccess();
 * 方法参照 InvoiceClientContract
 *
 * Class WmsClientServiceProvider
 * @package App\Services\Hub
 *
 * @author linqihai
 */
class WmsClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'wmsclient');
        $this->app->singleton('wmsclient', function ($app) {
            return new WmsClientManager($app);
        });
    }

    public function provides()
    {
        return ['wmsclient'];
    }

    protected function configPath()
    {
        return __DIR__ . '/config/wmsclient.php';
    }
}
