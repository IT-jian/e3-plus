<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () {
    return view('index');
});

$router->get('/test[/{method}]', 'TestController@index');

$router->post('login', 'AuthenticateController@login');
$router->post('refresh_token', 'AuthenticateController@refreshToken');

// 根据凭据获取token
$router->post('api_token', 'AuthenticateController@client');
// 客户端访问入口
$router->group(['middleware' => ['hub_api_log', 'app']], function () use ($router) {
    $router->post('/api', 'ApiController@index');
});
// 奇门接口
$router->group(['prefix' => 'api', 'middleware' => ['qimen_api_log'], 'namespace' => 'Api'], function () use ($router) {
    $router->post('/qimen', 'QimenController@index');
});
// Asino 发票更新接口 /
$router->group(['prefix' => 'api', 'middleware' => ['hub_api_log'], 'namespace' => 'Api'], function () use ($router) {
    $router->post('/alibaba_invoice', 'AlibabaInvoiceController@store');
});
