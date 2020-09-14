<?php


namespace App\Services\Adaptor\Contracts;


interface AdaptorContract
{
    public function platformType();

    public function download($type, $param);

    public function transfer($type, $param);
}