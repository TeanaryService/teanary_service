<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CustomAuthenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // 如果不是 JSON 请求，重定向到你定义的登录页
        if (! $request->expectsJson()) {
            return locaRoute('auth.login');
        }

        return null;
    }
}
