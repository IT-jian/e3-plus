<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);

 $app->routeMiddleware([
                           'app'    => App\Http\Middleware\AppMiddleware::class,
                           'cors'   => \Barryvdh\Cors\HandleCors::class,
                           'auth'   => App\Http\Middleware\Authenticate::class,
                           'client' => Laravel\Passport\Http\Middleware\CheckClientCredentials::class,

                           'permission'    => Spatie\Permission\Middlewares\PermissionMiddleware::class,
                           'role'          => Spatie\Permission\Middlewares\RoleMiddleware::class,
                           'operate_log'   => \App\Http\Middleware\OperationLogMiddleware::class,
                           'hub_api_log'   => \App\Http\Middleware\HubApiLogMiddleware::class,
                           'qimen_api_log' => \App\Http\Middleware\QimenApiLogMiddleware::class,
 ]);
/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
// Passport
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
// 跨域处理
$app->register(Barryvdh\Cors\ServiceProvider::class);
// horizon
$app->register(App\Providers\HorizonServiceProvider::class);
// 钉钉消息通知
$app->register(Calchen\LaravelDingtalkRobot\DingtalkRobotNoticeServiceProvider::class);
// sql debug
// $app->register(App\Providers\QueryLoggerProvider::class);
// auth 配置信息
$app->configure('app');
$app->configure('auth');
$app->configure('database');
$app->configure('cors');
$app->configure('horizon');
// swoole
$app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class);

// permission
$app->configure('permission');
$app->alias('cache', \Illuminate\Cache\CacheManager::class);  // if you don't have this already
$app->register(Spatie\Permission\PermissionServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
                        'namespace'  => 'App\Http\Controllers',
                        'middleware' => ['app'],
], function ($router) {
    require __DIR__.'/../routes/web.php';
    require __DIR__.'/../routes/admin.php'; // 后台路由
});

return $app;
