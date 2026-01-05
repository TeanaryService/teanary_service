<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 强制设置Accept头为application/json，确保API返回JSON格式
        $request->headers->set('Accept', 'application/json');
        
        $response = $next($request);
        
        // 如果响应不是JSON格式，转换为JSON
        if (!$response instanceof \Illuminate\Http\JsonResponse) {
            $contentType = $response->headers->get('Content-Type', '');
            if (!str_contains($contentType, 'application/json')) {
                return response()->json([
                    'message' => '服务器错误',
                    'error' => '响应格式错误',
                ], $response->getStatusCode());
            }
        }
        
        return $response;
    }
}

