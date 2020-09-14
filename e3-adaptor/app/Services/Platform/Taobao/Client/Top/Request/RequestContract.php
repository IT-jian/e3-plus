<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


interface RequestContract
{

    public function getApiMethodName();

    public function getApiParas();

    public function check();

    public function putOtherTextParam($key, $value);
}