<?php


namespace App\Services\Wms\Contracts;


interface WmsClientContract
{
    public function execute($requests);

    public function resolveRequestClass($method);
}
