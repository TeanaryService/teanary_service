<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomRedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // 根据 guard 类型重定向到不同的首页
                if ($guard === 'manager') {
                    return redirect()->to(locaRoute('manager.dashboard'));
                }

                // 默认重定向到用户首页（使用 locaRoute 确保包含 locale）
                return redirect()->to(locaRoute('home'));
            }
        }

        return $next($request);
    }
}
