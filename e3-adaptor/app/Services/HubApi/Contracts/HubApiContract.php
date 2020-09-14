<?php


namespace App\Services\HubApi\Contracts;


use Illuminate\Http\Request;

interface HubApiContract
{
    public function check(Request $request);

    /**
     * @param $name
     * @return \App\Services\HubApi\Contracts\HubPlatformApiContract
     *
     * @author linqihai
     * @since 2019/12/31 18:20
     */
    public function platform($name);
}