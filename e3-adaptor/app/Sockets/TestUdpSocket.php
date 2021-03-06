<?php


namespace App\Sockets;


use Hhxsv5\LaravelS\Swoole\Socket\UdpSocket;
use Swoole\Server;

class TestUdpSocket extends UdpSocket
{

    public function onPacket(Server $server, $data, array $clientInfo)
    {
        $server->sendto($clientInfo['address'], $clientInfo['port'], "Server " . $data);
        var_dump($clientInfo);
    }
}