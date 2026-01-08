<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait HandlesApiResponses
{
    /**
     * 返回成功响应.
     *
     * @param  string  $message  成功消息
     * @param  mixed  $data  响应数据
     * @param  int  $statusCode  HTTP状态码
     */
    protected function successResponse(string $message, $data = null, int $statusCode = 201): JsonResponse
    {
        $response = ['message' => $message];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * 返回错误响应.
     *
     * @param  string  $message  错误消息
     * @param  string|null  $error  详细错误信息（仅在调试模式下显示）
     * @param  int  $statusCode  HTTP状态码
     */
    protected function errorResponse(string $message, ?string $error = null, int $statusCode = 500): JsonResponse
    {
        $response = ['message' => $message];

        if ($error && config('app.debug')) {
            $response['error'] = $error;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * 记录错误并返回错误响应.
     *
     * @param  \Exception  $e  异常对象
     * @param  string  $context  上下文信息（如 '文章创建失败'）
     * @param  array  $contextData  额外的上下文数据
     */
    protected function handleException(\Exception $e, string $context, array $contextData = []): JsonResponse
    {
        Log::error($context.'：'.$e->getMessage(), array_merge([
            'trace' => $e->getTraceAsString(),
        ], $contextData));

        return $this->errorResponse(
            $context,
            config('app.debug') ? $e->getMessage() : '系统错误'
        );
    }
}
