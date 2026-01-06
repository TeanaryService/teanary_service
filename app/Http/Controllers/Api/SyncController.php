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
    ) {
    }

    /**
     * 接收来自远程节点的同步数据
     */
    public function receive(Request $request): JsonResponse
    {
        // 验证 API Key
        $apiKey = $request->header('Authorization');
        $apiKey = str_replace('Bearer ', '', $apiKey);
        
        $config = config('sync');
        $sourceNode = $request->header('X-Sync-Source-Node');
        
        // 验证来源节点和 API Key
        if (!$sourceNode || !isset($config['remote_nodes'][$sourceNode])) {
            return response()->json([
                'success' => false,
                'message' => '无效的来源节点',
            ], 403);
        }

        $remoteConfig = $config['remote_nodes'][$sourceNode];
        if ($apiKey !== $remoteConfig['api_key']) {
            return response()->json([
                'success' => false,
                'message' => '无效的 API Key',
            ], 403);
        }

        // 验证请求数据
        $validator = Validator::make($request->all(), [
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'action' => 'required|in:created,updated,deleted',
            'payload' => 'required|array',
            'source_node' => 'required|string',
            'timestamp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '请求数据验证失败',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $this->syncService->receiveSync($request->all());

            return response()->json([
                'success' => true,
                'message' => '同步成功',
            ]);
        } catch (\Exception $e) {
            Log::error('接收同步数据失败', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '同步失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取同步状态（健康检查）
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
     * 下载文件（用于同步）
     */
    public function downloadFile(Request $request, int $mediaId, ?string $conversion = null): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
    {
        $token = $request->query('token');
        
        if (!$token || !$this->syncService->verifyFileDownloadToken($token, $mediaId)) {
            return response()->json([
                'success' => false,
                'message' => '无效或过期的下载令牌',
            ], 403);
        }

        try {
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);
            
            $disk = \Illuminate\Support\Facades\Storage::disk($media->disk);
            
            // 如果是转换文件
            if ($conversion) {
                $filePath = $media->getPath($conversion);
                $fileName = $media->name . '-' . $conversion . '.' . pathinfo($media->file_name, PATHINFO_EXTENSION);
            } else {
                $filePath = $media->getPath();
                $fileName = $media->file_name;
            }
            
            if (!$disk->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '文件不存在',
                ], 404);
            }

            return $disk->download($filePath, $fileName);
        } catch (\Exception $e) {
            Log::error('下载文件失败', [
                'media_id' => $mediaId,
                'conversion' => $conversion,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '下载文件失败: ' . $e->getMessage(),
            ], 500);
        }
    }
}
