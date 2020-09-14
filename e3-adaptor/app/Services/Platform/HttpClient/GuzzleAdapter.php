<?php


namespace App\Services\Platform\HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

class GuzzleAdapter
{
    protected $httpClient;
    protected $streamHandler;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        if (class_exists('\Swoole\Coroutine') && $client->getConfig('force_handler_over_ride')) {
            $this->streamHandler = HandlerStack::create(new \GuzzleHttp\Handler\StreamHandler());
        }
    }

    /**
     * @param $requests \Psr\Http\Message\RequestInterface[]
     * @param null $timeout
     *
     * @return array|mixed
     * @throws \Throwable
     */
    public function send($requests, $timeout = null)
    {
        $requests = (array)$requests;
        $config = [];
        if ($this->streamHandler && (\Swoole\Coroutine::getuid() > 1)) {
            $config['handler'] = $this->streamHandler;
        }
        if (null !== $timeout) {
            $config['timeout'] = $timeout;
        }
        //这里将来改用连接池实现 异步请求
        foreach ((array)$requests as $key => $request) {
            /**
             * @var $request \Psr\Http\Message\RequestInterface
             */
            if ('https' == $request->getUri()->getScheme()) {
                $config['verify'] = false;
            }
            $requests[$key] = $this->httpClient->sendAsync($request, $config)->then(
                function (ResponseInterface $res) {
                    return $res;
                },
                function (RequestException $e) { // 捕捉报错
                    return $e;
                }
            );
        }

        return \GuzzleHttp\Promise\unwrap($requests);
    }
}
