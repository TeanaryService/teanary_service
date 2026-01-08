<?php

namespace App\Http\Middleware;

use App\Models\Manager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');

        // 支持 Bearer token 格式
        if ($token && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        // 如果没有在header中，尝试从query参数获取
        if (! $token) {
            $token = $request->query('token');
        }

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => '未提供访问令牌',
            ], 401);
        }

        $manager = Manager::where('token', $token)->first();

        if (! $manager) {
            return response()->json([
                'success' => false,
                'message' => '无效的访问令牌',
            ], 401);
        }

        // 将manager注入到request中，方便后续使用
        $request->merge(['authenticated_manager' => $manager]);

        return $next($request);
    }
}
