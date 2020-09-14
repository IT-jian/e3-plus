<?php


namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;

class AppMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 多语言支持
        app('translator')->setLocale($request->header('lang', 'en'));

        return $next($request);
    }
}