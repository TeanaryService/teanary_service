<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CustomAuthenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // 如果不是 JSON 请求，重定向到你定义的登录页
        if (! $request->expectsJson()) {
            // 检查是否是 manager guard
            if ($request->routeIs('manager.*')) {
                return locaRoute('manager.login');
            }

            return locaRoute('auth.login');
        }

        return null;
    }
}
