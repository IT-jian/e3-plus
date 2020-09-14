<?php


namespace App\Services\Platform;


use GuzzleHttp\Client;
use Laravel\Lumen\Application;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractHttpApiClient
{
    protected $app;
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    /**
     * @param \GuzzleHttp\Psr7\Request ||  \GuzzleHttp\Psr7\Request[]  $request
     *
     * @return mixed
     */
    abstract public function send($request);
    abstract public function parseResponse(ResponseInterface $response);
}