<?php

namespace App\Services;

use App\Models\SyncLog;
use App\Models\SyncStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncService
{
    /**
     * 记录需要同步的数据变更
     */
    public function recordSync(
        Model $model,
        string $action,
        string $sourceNode
    ): void {
        if (!$this->shouldSync($model)) {
            return;
        }

        $config = config('sync');
        $currentNode = $config['node'];
        
        // 获取所有需要同步的目标节点（除了当前节点）
        $targetNodes = array_keys($config['remote_nodes']);
        $targetNodes = array_filter($targetNodes, fn($node) => $node !== $currentNode);

        foreach ($targetNodes as $targetNode) {
            // 检查是否已经同步过（避免重复同步）
            $syncHash = $this->generateSyncHash($model, $action);
            
            if ($action !== 'deleted' && !SyncStatus::needsSync(
                get_class($model),
                $model->id,
                $targetNode,
                $syncHash
            )) {
                continue; // 数据未变更，跳过
            }

            SyncLog::create([
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'action' => $action,
                'source_node' => $sourceNode,
                'target_node' => $targetNode,
                'status' => 'pending',
                'payload' => $this->preparePayload($model, $action),
            ]);
        }
    }

    /**
     * 执行同步到远程节点
     */
    public function syncToRemote(SyncLog $syncLog): bool
    {
        try {
            $syncLog->markAsProcessing();

            $config = config('sync');
            $targetNode = $syncLog->target_node;
            $remoteConfig = $config['remote_nodes'][$targetNode] ?? null;

            if (!$remoteConfig || !$remoteConfig['api_key']) {
                throw new \Exception("远程节点配置不存在或未设置 API Key: {$targetNode}");
            }

            $response = Http::timeout($remoteConfig['timeout'])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $remoteConfig['api_key'],
                    'Content-Type' => 'application/json',
                    'X-Sync-Source-Node' => config('sync.node'),
                ])
                ->post($remoteConfig['url'] . '/api/sync/receive', [
                    'model_type' => $syncLog->model_type,
                    'model_id' => $syncLog->model_id,
                    'action' => $syncLog->action,
                    'payload' => $syncLog->payload,
                    'source_node' => $syncLog->source_node,
                    'timestamp' => $syncLog->created_at->toIso8601String(),
                ]);

            if ($response->successful()) {
                $syncLog->markAsCompleted();
                
                // 更新同步状态
                if ($syncLog->action !== 'deleted') {
                    $model = $syncLog->model_type::find($syncLog->model_id);
                    if ($model) {
                        $syncHash = $this->generateSyncHash($model, $syncLog->action);
                        SyncStatus::updateSyncStatus(
                            $syncLog->model_type,
                            $syncLog->model_id,
                            $targetNode,
                            $syncHash
                        );
                    }
                }

                return true;
            } else {
                throw new \Exception("同步失败: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('同步失败', [
                'sync_log_id' => $syncLog->id,
                'error' => $e->getMessage(),
            ]);

            $syncLog->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * 接收来自远程节点的同步数据
     */
    public function receiveSync(array $data): bool
    {
        try {
            $modelType = $data['model_type'];
            $modelId = $data['model_id'];
            $action = $data['action'];
            $payload = $data['payload'];
            $sourceNode = $data['source_node'];
            $timestamp = $data['timestamp'] ?? now()->toIso8601String();

            // 检查模型是否在同步列表中
            if (!in_array($modelType, config('sync.sync_models'))) {
                throw new \Exception("模型不在同步列表中: {$modelType}");
            }

            // 检查时间戳，确保以最新为准
            $existingModel = $modelType::find($modelId);
            if ($existingModel && $existingModel->updated_at) {
                $remoteTimestamp = Carbon::parse($timestamp);
                if ($existingModel->updated_at->gt($remoteTimestamp)) {
                    // 本地数据更新，忽略远程同步
                    return true;
                }
            }

            // 禁用同步监听，避免循环同步
            $this->disableSyncForModel($modelType);

            switch ($action) {
                case 'created':
                case 'updated':
                    $model = $this->createOrUpdateModel($modelType, $modelId, $payload);
                    
                    // 如果是 Media 模型，需要下载文件
                    if ($model instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
                        $this->downloadAndSaveMediaFile($model, $payload);
                    }
                    break;
                case 'deleted':
                    $this->deleteModel($modelType, $modelId);
                    break;
            }

            // 重新启用同步监听
            $this->enableSyncForModel($modelType);

            return true;
        } catch (\Exception $e) {
            Log::error('接收同步数据失败', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 检查模型是否应该同步
     */
    protected function shouldSync(Model $model): bool
    {
        if (!config('sync.enabled')) {
            return false;
        }

        $syncModels = config('sync.sync_models', []);
        return in_array(get_class($model), $syncModels);
    }

    /**
     * 准备同步数据
     */
    protected function preparePayload(Model $model, string $action): array
    {
        if ($action === 'deleted') {
            return [
                'id' => $model->id,
                'deleted_at' => now()->toIso8601String(),
            ];
        }

        // 获取模型的所有属性（包括关联数据）
        $payload = $model->toArray();
        
        // 处理时间戳
        if (isset($payload['created_at'])) {
            $payload['created_at'] = $model->created_at->toIso8601String();
        }
        if (isset($payload['updated_at'])) {
            $payload['updated_at'] = $model->updated_at->toIso8601String();
        }

        // 如果是 Media 模型，添加文件 URL 和下载信息
        if ($model instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            $payload['file_url'] = $model->getUrl();
            $payload['file_path'] = $model->getPath();
            $payload['file_disk'] = $model->disk;
            $payload['file_download_url'] = $this->generateFileDownloadUrl($model);
        }

        return $payload;
    }

    /**
     * 生成文件下载 URL（用于从源节点下载文件）
     */
    protected function generateFileDownloadUrl(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
    {
        $sourceNode = config('sync.node');
        $baseUrl = config('app.url');
        
        return $baseUrl . '/api/sync/download-file/' . $media->id . '?token=' . $this->generateFileDownloadToken($media);
    }

    /**
     * 生成文件下载令牌
     */
    protected function generateFileDownloadToken(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
    {
        $payload = [
            'media_id' => $media->id,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ];
        
        return base64_encode(json_encode($payload));
    }

    /**
     * 验证文件下载令牌
     */
    public function verifyFileDownloadToken(string $token, int $mediaId): bool
    {
        try {
            $payload = json_decode(base64_decode($token), true);
            
            if (!$payload || !isset($payload['media_id']) || !isset($payload['expires_at'])) {
                return false;
            }
            
            if ($payload['media_id'] !== $mediaId) {
                return false;
            }
            
            if ($payload['expires_at'] < now()->timestamp) {
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 生成同步哈希值
     */
    protected function generateSyncHash(Model $model, string $action): string
    {
        $data = $this->preparePayload($model, $action);
        return md5(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 创建或更新模型
     */
    protected function createOrUpdateModel(string $modelType, int $modelId, array $payload): Model
    {
        // 对于 Media 模型，需要特殊处理（移除文件相关字段，稍后单独处理）
        $fileFields = ['file_url', 'file_path', 'file_disk', 'file_download_url'];
        $mediaPayload = $payload;
        if ($modelType === \Spatie\MediaLibrary\MediaCollections\Models\Media::class) {
            $mediaPayload = array_diff_key($payload, array_flip($fileFields));
        }
        
        $model = $modelType::find($modelId);
        
        if ($model) {
            // 更新时间戳，确保以最新为准
            if (isset($mediaPayload['updated_at'])) {
                $mediaPayload['updated_at'] = Carbon::parse($mediaPayload['updated_at']);
            }
            if (isset($mediaPayload['created_at'])) {
                $mediaPayload['created_at'] = Carbon::parse($mediaPayload['created_at']);
            }
            $model->update($mediaPayload);
        } else {
            $mediaPayload['id'] = $modelId; // 保持原始ID
            if (isset($mediaPayload['created_at'])) {
                $mediaPayload['created_at'] = Carbon::parse($mediaPayload['created_at']);
            }
            if (isset($mediaPayload['updated_at'])) {
                $mediaPayload['updated_at'] = Carbon::parse($mediaPayload['updated_at']);
            }
            $model = $modelType::create($mediaPayload);
        }
        
        return $model;
    }

    /**
     * 下载并保存 Media 文件
     */
    protected function downloadAndSaveMediaFile(
        \Spatie\MediaLibrary\MediaCollections\Models\Media $media,
        array $payload
    ): void {
        if (!isset($payload['file_download_url'])) {
            Log::warning('Media 同步数据缺少文件下载 URL', [
                'media_id' => $media->id,
            ]);
            return;
        }

        try {
            // 从源节点下载文件
            $response = Http::timeout(300) // 5分钟超时，用于大文件
                ->get($payload['file_download_url']);

            if (!$response->successful()) {
                throw new \Exception("下载文件失败: HTTP " . $response->status());
            }

            // 获取文件内容
            $fileContent = $response->body();
            
            // 保存文件到本地
            $disk = $media->disk ?? config('media-library.disk_name', 'public');
            $diskInstance = \Illuminate\Support\Facades\Storage::disk($disk);
            
            // 使用 Media Library 的方法获取正确的文件路径
            $filePath = $media->getPath();
            
            // 确保目录存在
            $directory = dirname($filePath);
            if (!$diskInstance->exists($directory)) {
                $diskInstance->makeDirectory($directory, 0755, true);
            }
            
            // 保存文件
            $diskInstance->put($filePath, $fileContent);
            
            // 如果存在转换文件，也需要下载
            if (isset($payload['generated_conversions']) && is_array($payload['generated_conversions'])) {
                foreach ($payload['generated_conversions'] as $conversionName => $converted) {
                    if ($converted) {
                        try {
                            // 构建转换文件的下载 URL
                            $baseUrl = parse_url($payload['file_download_url'], PHP_URL_SCHEME) . '://' . parse_url($payload['file_download_url'], PHP_URL_HOST);
                            $token = parse_url($payload['file_download_url'], PHP_URL_QUERY);
                            $conversionUrl = $baseUrl . '/api/sync/download-file/' . $media->id . '/conversion/' . $conversionName . '?' . $token;
                            
                            $conversionResponse = Http::timeout(300)->get($conversionUrl);
                            if ($conversionResponse->successful()) {
                                $conversionPath = $media->getPath($conversionName);
                                $conversionDir = dirname($conversionPath);
                                if (!$diskInstance->exists($conversionDir)) {
                                    $diskInstance->makeDirectory($conversionDir, 0755, true);
                                }
                                $diskInstance->put($conversionPath, $conversionResponse->body());
                            }
                        } catch (\Exception $e) {
                            Log::warning('下载转换文件失败', [
                                'media_id' => $media->id,
                                'conversion' => $conversionName,
                                'error' => $e->getMessage(),
                            ]);
                            // 转换文件下载失败不影响主文件同步
                        }
                    }
                }
            }
            
            Log::info('Media 文件同步成功', [
                'media_id' => $media->id,
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            Log::error('下载 Media 文件失败', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 删除模型
     */
    protected function deleteModel(string $modelType, int $modelId): void
    {
        $model = $modelType::find($modelId);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * 临时禁用模型的同步监听
     */
    protected function disableSyncForModel(string $modelType): void
    {
        // 使用静态属性标记，在 Trait 或 Observer 中检查
        if ($modelType === \Spatie\MediaLibrary\MediaCollections\Models\Media::class) {
            \App\Observers\MediaObserver::$syncDisabled = true;
        } else {
            $modelType::$syncDisabled = true;
        }
    }

    /**
     * 重新启用模型的同步监听
     */
    protected function enableSyncForModel(string $modelType): void
    {
        if ($modelType === \Spatie\MediaLibrary\MediaCollections\Models\Media::class) {
            \App\Observers\MediaObserver::$syncDisabled = false;
        } else {
            $modelType::$syncDisabled = false;
        }
    }
}
