<?php

namespace App\Http\Controllers;

use App\Api\Helpers\ApiResponse;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ApiResponse;

    public function responsePaginator($paginate)
    {
        $page = $paginate;
        if ($paginate instanceof Paginator) {
            $page = $paginate->toArray();
        }
        return [
            'data' => $page['data'],
            'meta' => [
                'pagination' => [
                    'count' => $page['total'],
                    'current_page' => $page['current_page'],
                    'per_page' => $page['per_page'],
                    'total' => $page['total'],
                    'total_pages' => ceil($page['total']/$page['per_page'])
                ]
            ]
        ];
    }

    /**
     * @return \App\Models\User
     *
     * @author linqihai
     * @since 2019/11/19 10:29
     */
    public function user()
    {
        return Auth::user();
    }
}
