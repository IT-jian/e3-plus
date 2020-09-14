<?php


namespace App\Sockets;


use App\Tasks\TestLogTask;
use Hhxsv5\LaravelS\Swoole\Socket\Http;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Swoole\Http\Request;
use Swoole\Http\Response;

class TestHttp extends Http
{

    public function onRequest(Request $request, Response $response)
    {
        var_dump($request->get, $request->post, $request->rawContent());
        Task::deliver(new TestLogTask($request->rawContent()));

        $response->header("Content-Type", "text/html; charset=utf-8");
        $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
    }
}