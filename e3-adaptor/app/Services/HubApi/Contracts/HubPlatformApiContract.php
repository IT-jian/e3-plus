<?php


namespace App\Services\HubApi\Contracts;


interface HubPlatformApiContract
{
    public function execute($params);
    public function mock($params);
}