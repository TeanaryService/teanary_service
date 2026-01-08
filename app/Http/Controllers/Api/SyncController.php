<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
    public function __construct(
        protected SyncService $syncService
    ) {}

    /**
     * 获取同步状态（健康检查）.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'node' => config('sync.node'),
            'enabled' => config('sync.enabled'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * 手动触发模型同步
     * 用于在直接操作数据库后触发同步.
     */
    public function triggerSync(Request $request): JsonResponse
    {
        // 验证 API Key
        $apiKey = $request->header('Authorization');
        $apiKey = str_replace('Bearer ', '', $apiKey);

        $config = config('sync');

        // 验证 API Key（使用任一远程节点的 API Key）
        $validApiKey = false;
        foreach ($config['remote_nodes'] as $nodeConfig) {
            if ($apiKey === $nodeConfig['api_key']) {
                $validApiKey = true;
                break;
            }
        }

        if (! $validApiKey) {
            return response()->json([
                'success' => false,
                'message' => '无效的 API Key',
            ], 403);
        }

        // 验证请求数据
        $validator = Validator::make($request->all(), [
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'action' => 'required|in:created,updated',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '请求数据验证失败',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $modelType = $request->input('model_type');
            $modelId = $request->input('model_id');
            $action = $request->input('action');

            // 检查模型类是否存在
            if (! class_exists($modelType)) {
                return response()->json([
                    'success' => false,
                    'message' => "模型类不存在: {$modelType}",
                ], 404);
            }

            // 查找模型实例
            $model = $modelType::find($modelId);
            if (! $model) {
                return response()->json([
                    'success' => false,
                    'message' => "模型实例不存在: {$modelType}::{$modelId}",
                ], 404);
            }

            // 手动触发同步
            $this->syncService->recordSync($model, $action, config('sync.node'));

            return response()->json([
                'success' => true,
                'message' => '同步已触发',
            ]);
        } catch (\Exception $e) {
            Log::error('手动触发同步失败', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '触发同步失败: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * 批量接收来自远程节点的同步数据（高效方案）.
     */
    public function receiveBatch(Request $request): JsonResponse
    {
        // 验证 API Key
        $apiKey = $request->header('Authorization');
        $apiKey = str_replace('Bearer ', '', $apiKey);

        $config = config('sync');
        $sourceNode = $request->header('X-Sync-Source-Node');

        // 验证来源节点和 API Key
        if (! $sourceNode || ! isset($config['remote_nodes'][$sourceNode])) {
            Log::error('无效的来源节点', [
                'source_node' => $sourceNode,
            ]);

            return response()->json([
                'success' => false,
                'message' => '无效的来源节点',
            ], 403);
        }

        $remoteConfig = $config['remote_nodes'][$sourceNode];
        if ($apiKey !== $remoteConfig['api_key']) {
            Log::error('无效的 API Key');

            return response()->json([
                'success' => false,
                'message' => '无效的 API Key',
            ], 403);
        }

        // 验证请求数据
        $validator = Validator::make($request->all(), [
            'batch' => 'required|array|min:1|max:100', // 最多100条
            'batch.*.model_type' => 'required|string',
            'batch.*.model_id' => 'required|integer',
            'batch.*.action' => 'required|in:created,updated,deleted',
            'batch.*.payload' => 'required|array',
            'batch.*.source_node' => 'required|string',
            'batch.*.timestamp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '请求数据验证失败',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->syncService->receiveBatchSync($request->input('batch'));

            return response()->json([
                'success' => true,
                'message' => '批量同步完成',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('批量接收同步数据失败', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '批量同步失败: '.$e->getMessage(),
            ], 500);
        }
    }
}
