<?php


namespace App\Jobs;


use App\Services\Platform\HttpClient\GuzzleAdapter;

class TradeCancelPushWmsJob extends Job
{
    private $params;


    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $params = [
            'orderCode' => '',
            'orderLineNos' => [
                'lineNo' => ''
            ]
        ];
    }

    public function send($requests, $timeout = 10)
    {
        $adaptor = app()->make(GuzzleAdapter::class);

        return $adaptor->send($requests, $timeout);
    }
}
