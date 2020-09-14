<?php


namespace App\Services\Platform\Contracts\Shop;


interface AuthorizationContracts
{
    public function call($shopId);
    public function callback(\Illuminate\Http\Request $request);
    public function refresh($shopId);
}