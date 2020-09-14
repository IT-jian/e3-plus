<?php


namespace App\Services\Wms\Baozun\Adidas;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use function GuzzleHttp\Psr7\stream_for;

class BaseRequest extends \App\Services\Wms\Shunfeng\Adidas\BaseRequest
{
    public $type = 'baozun';
}
