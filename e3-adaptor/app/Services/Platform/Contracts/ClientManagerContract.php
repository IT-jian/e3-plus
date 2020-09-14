<?php


namespace App\Services\Platform\Contracts;


interface ClientManagerContract
{
    public function shop($code);

    public function makeClient($code);
}