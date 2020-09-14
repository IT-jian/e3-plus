<?php


namespace App\Services\Hub\Contracts;


interface HubClientContract
{
    public function platform($platform);

    public function execute($requests);

    public function resolveRequestClass($method);
}