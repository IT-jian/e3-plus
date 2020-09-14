<?php


namespace App\Services\Adaptor;


use App\Services\Platform\Jingdong\Client\Jos\JosClientManager;
use App\Services\Platform\Taobao\Client\Top\TopClientManager;
use App\Services\Platform\Taobao\Qimen\Top\QimenClientManager;
use Illuminate\Support\ServiceProvider;

class AdaptorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'adaptor');

        $this->app->singleton('adaptor', function ($app) {
            return new AdaptorManager($app);
        });
        // 淘宝开放平台 sdk
        $this->app->singleton('topclient', function () {
            return new TopClientManager();
        });
        // 京东开放平台 sdk
        $this->app->singleton('josclient', function () {
            return new JosClientManager();
        });
        // 淘宝开放平台 奇门
        $this->app->singleton('qimenclient', function () {
            return new QimenClientManager();
        });
        // 注册命令行
        if ($this->app->runningInConsole()) {
            // $this->commands([ ]);
        }
    }

    protected function configPath()
    {
        return __DIR__.'/config/adaptor.php';
    }

    public function provides()
    {
        return ['topclient', 'josclient', 'qimenclient'];
    }
}
