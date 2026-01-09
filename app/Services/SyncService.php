<?php

namespace App\Services;

use App\Jobs\ResizeUploadedImage;
use App\Models\SyncLog;
use App\Models\SyncStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 数据同步服务（已优化：利用雪花ID全局唯一性，同步接近实时）.
 *
 * 优化点：
 * 1. 使用雪花ID，ID全局唯一，直接通过ID查找即可，无需通过唯一字段查找
 * 2. 同步接近实时，唯一字段冲突（sku、slug等）不会发生，无需处理
 * 3. 移除了外键约束，外键约束不会失败
 * 4. 直接根据payload的ID插入或覆盖，简化逻辑
 * 5. 批量同步更高效，可以直接批量插入/更新
 * 6. 保留基本的payload清理（移除关系数据和非fillable字段），因为toArray可能包含关系数据
 */
class SyncService
{
    /**
     * 记录需要同步的数据变更.
     */
    public function recordSync(
        Model $model,
        string $action,
        string $sourceNode
    ): void {
        if (! $this->shouldSync($model)) {
            return;
        }

        $targetNodes = $this->getTargetNodes();
        $modelType = get_class($model);
        $payload = $this->preparePayload($model, $action);
        $syncHash = $this->generateSyncHash($model, $action);

        foreach ($targetNodes as $targetNode) {
            if (! $this->shouldCreateSyncLog($modelType, $model->id, $action, $targetNode, $syncHash)) {
                continue;
            }

            $this->createSyncLog($modelType, $model->id, $action, $sourceNode, $targetNode, $payload);
        }
    }

    /**
     * 批量记录需要同步的数据变更
     * 用于批量操作时提高效率.
     *
     * @param  array  $models  模型数组，格式: [['model' => Model, 'action' => 'updated'], ...]
     * @param  string  $sourceNode  源节点
     */
    public function recordBatchSync(array $models, string $sourceNode): void
    {
        if (empty($models)) {
            return;
        }

        $targetNodes = $this->getTargetNodes();
        if (empty($targetNodes)) {
            return;
        }

        $groupedModels = $this->groupModelsForBatchSync($models);
        $syncLogs = $this->buildBatchSyncLogs($groupedModels, $targetNodes, $sourceNode);

        $this->insertBatchSyncLogs($syncLogs);
    }

    /**
     * 批量同步到远程节点
     * 将多条记录打包成一个请求，大幅提升效率.
     *
     * @param  \Illuminate\Support\Collection  $syncLogs  待同步的日志集合
     * @param  string  $targetNode  目标节点
     * @return array ['success' => int, 'failed' => int, 'errors' => array]
     */
    public function syncBatchToRemote(\Illuminate\Support\Collection $syncLogs, string $targetNode): array
    {
        if ($syncLogs->isEmpty()) {
            return ['success' => 0, 'failed' => 0, 'errors' => []];
        }

        try {
            $remoteConfig = $this->getRemoteNodeConfig($targetNode);

            // 标记所有记录为处理中
            $syncLogs->each(function ($syncLog) {
                $syncLog->markAsProcessing();
            });

            // 准备批量数据并按依赖关系排序
            $batchData = $syncLogs->map(function ($syncLog) {
                return [
                    'model_type' => $syncLog->model_type,
                    'model_id' => $syncLog->model_id,
                    'action' => $syncLog->action,
                    'payload' => $syncLog->payload,
                    'source_node' => $syncLog->source_node,
                    'timestamp' => $syncLog->created_at->toIso8601String(),
                    'sync_log_id' => $syncLog->id, // 用于标识返回结果
                ];
            })->values()->toArray();

            // 发送批量请求
            $response = $this->sendBatchSyncRequest($batchData, $remoteConfig);

            if ($response->successful()) {
                $result = $response->json();

                return $this->handleBatchSyncResult($syncLogs, $result, $targetNode);
            } else {
                throw new \Exception('批量同步失败: '.$response->body());
            }
        } catch (\Exception $e) {
            Log::error('批量同步失败', [
                'target_node' => $targetNode,
                'count' => $syncLogs->count(),
                'error' => $e->getMessage(),
            ]);

            // 标记所有为失败
            $syncLogs->each(function ($syncLog) use ($e) {
                $this->handleSyncFailure($syncLog, $e);
            });

            return [
                'success' => 0,
                'failed' => $syncLogs->count(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * 批量接收来自远程节点的同步数据.
     *
     * @param  array  $batchData  批量数据数组
     * @return array ['success' => int, 'failed' => int, 'results' => array]
     */
    public function receiveBatchSync(array $batchData): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'results' => [],
        ];

        if (empty($batchData) || ! is_array($batchData)) {
            return $results;
        }

        // 按模型类型分组，减少开关同步监听的次数
        $groupedByModelType = [];
        foreach ($batchData as $index => $data) {
            $modelType = $data['model_type'] ?? null;
            if (! $modelType) {
                ++$results['failed'];
                $results['results'][] = [
                    'index' => $index,
                    'sync_log_id' => $data['sync_log_id'] ?? null,
                    'success' => false,
                    'error' => '缺少 model_type',
                ];
                continue;
            }

            if (! isset($groupedByModelType[$modelType])) {
                $groupedByModelType[$modelType] = [];
            }

            $groupedByModelType[$modelType][] = [
                'index' => $index,
                'data' => $data,
            ];
        }

        // 按模型类型批量处理
        foreach ($groupedByModelType as $modelType => $items) {
            $this->disableSyncForModel($modelType);

            try {
                foreach ($items as $item) {
                    $index = $item['index'];
                    $data = $item['data'];

                    try {
                        $this->validateSyncData($data);

                        if ($this->shouldSkipSync($data)) {
                            ++$results['success'];
                            $results['results'][] = [
                                'index' => $index,
                                'sync_log_id' => $data['sync_log_id'] ?? null,
                                'success' => true,
                                'skipped' => true,
                            ];
                            continue;
                        }

                        $this->processSyncAction($data);

                        ++$results['success'];
                        $results['results'][] = [
                            'index' => $index,
                            'sync_log_id' => $data['sync_log_id'] ?? null,
                            'success' => true,
                        ];
                    } catch (\Exception $e) {
                        ++$results['failed'];
                        $results['results'][] = [
                            'index' => $index,
                            'sync_log_id' => $data['sync_log_id'] ?? null,
                            'success' => false,
                            'error' => $e->getMessage(),
                        ];

                        Log::error('批量同步中单条记录处理失败', [
                            'index' => $index,
                            'data' => $data,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } finally {
                $this->enableSyncForModel($modelType);
            }
        }

        // 清除缓存（只清除一次）
        Cache::flush();

        return $results;
    }

    /**
     * 检查模型是否应该同步.
     */
    protected function shouldSync(Model $model): bool
    {
        if (! config('sync.enabled')) {
            return false;
        }

        $syncModels = config('sync.sync_models', []);

        return in_array(get_class($model), $syncModels);
    }

    /**
     * 准备同步数据.
     */
    protected function preparePayload(Model $model, string $action): array
    {
        if ($action === 'deleted') {
            return [
                'id' => $model->id,
                'deleted_at' => now()->toIso8601String(),
            ];
        }

        $payload = $model->toArray();
        $this->normalizeTimestampsInPayload($payload, $model);
        $this->addMediaFileInfo($payload, $model);

        return $payload;
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
     * 创建或更新模型（优化版：利用雪花ID全局唯一性，同步接近实时）.
     *
     * 优化点：
     * - 使用雪花ID，ID全局唯一，直接通过ID查找即可
     * - 同步接近实时，无需处理唯一字段冲突（sku、slug等）
     * - 直接根据ID插入或覆盖，简化逻辑
     */
    protected function createOrUpdateModel(string $modelType, int $modelId, array $payload): ?Model
    {
        // 基本清理：移除关系数据和非fillable字段（toArray可能包含关系数据）
        $cleanPayload = $this->cleanPayloadForModel($modelType, $payload);

        // 直接通过雪花ID查找（全局唯一）
        $model = $modelType::find($modelId);

        if ($model) {
            // 模型已存在，直接更新覆盖
            return $this->updateExistingModel($model, $cleanPayload);
        }

        // 模型不存在，创建新记录（使用原始雪花ID）
        return $this->createNewModel($modelType, $modelId, $cleanPayload);
    }

    /**
     * 下载并保存 Media 文件.
     */
    protected function downloadAndSaveMediaFile(
        \App\Models\Media $media,
        array $payload
    ): void {
        // 优先使用 original_url，如果没有则尝试 file_url
        $downloadUrl = $payload['original_url'] ?? $payload['file_url'] ?? null;
        
        if (! $downloadUrl) {
            Log::warning('Media 同步数据缺少下载 URL', [
                'media_id' => $media->id,
                'payload_keys' => array_keys($payload),
                'payload' => $payload,
            ]);

            return;
        }

        // 检查文件是否已存在
        if ($this->mediaFileExists($media)) {
            Log::info('Media 文件已存在，跳过下载', [
                'media_id' => $media->id,
                'file_path' => $this->getMediaFilePath($media),
            ]);
            
            // 即使文件存在，也触发转换和调整任务
            $this->triggerMediaConversions($media);
            $this->dispatchImageResizeJob($media);
            
            return;
        }

        try {
            $this->downloadMainMediaFile($media, $downloadUrl);
            $this->triggerMediaConversions($media);
            $this->dispatchImageResizeJob($media);

            Log::info('Media 文件同步成功', [
                'media_id' => $media->id,
                'file_path' => $this->getMediaFilePath($media),
                'file_size' => $this->getMediaFileSize($media),
            ]);
        } catch (\Exception $e) {
            Log::error('下载 Media 文件失败', [
                'media_id' => $media->id,
                'url' => $downloadUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * 删除模型.
     */
    protected function deleteModel(string $modelType, int $modelId): void
    {
        $model = $modelType::find($modelId);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * 临时禁用模型的同步监听.
     */
    protected function disableSyncForModel(string $modelType): void
    {
        // 检查类是否存在
        if (! class_exists($modelType)) {
            return;
        }

        // 使用静态属性标记，在 Trait 或 Observer 中检查
        if ($modelType === \App\Models\Media::class
            || is_subclass_of($modelType, \App\Models\Media::class)) {
            \App\Observers\MediaObserver::$syncDisabled = true;
        } else {
            $modelType::$syncDisabled = true;
        }
    }

    /**
     * 重新启用模型的同步监听.
     */
    protected function enableSyncForModel(string $modelType): void
    {
        // 检查类是否存在
        if (! class_exists($modelType)) {
            return;
        }

        if ($modelType === \App\Models\Media::class
            || is_subclass_of($modelType, \App\Models\Media::class)) {
            \App\Observers\MediaObserver::$syncDisabled = false;
        } else {
            $modelType::$syncDisabled = false;
        }
    }

    /**
     * 获取目标节点列表（排除当前节点）.
     */
    protected function getTargetNodes(): array
    {
        $config = config('sync');
        $currentNode = $config['node'];
        $targetNodes = array_keys($config['remote_nodes']);

        return array_filter($targetNodes, fn ($node) => $node !== $currentNode);
    }

    /**
     * 判断是否应该创建同步日志.
     */
    protected function shouldCreateSyncLog(
        string $modelType,
        int $modelId,
        string $action,
        string $targetNode,
        string $syncHash
    ): bool {
        if ($action === 'deleted') {
            return true;
        }

        return SyncStatus::needsSync($modelType, $modelId, $targetNode, $syncHash);
    }

    /**
     * 创建同步日志.
     */
    protected function createSyncLog(
        string $modelType,
        int $modelId,
        string $action,
        string $sourceNode,
        string $targetNode,
        array $payload
    ): void {
        SyncLog::create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'source_node' => $sourceNode,
            'target_node' => $targetNode,
            'status' => 'pending',
            'payload' => $payload,
        ]);
    }

    /**
     * 分组模型用于批量同步.
     */
    protected function groupModelsForBatchSync(array $models): array
    {
        $groupedModels = [];

        foreach ($models as $item) {
            $model = $item['model'];
            $action = $item['action'];

            if (! $this->shouldSync($model)) {
                continue;
            }

            $modelType = get_class($model);
            $key = "{$modelType}:{$action}";

            if (! isset($groupedModels[$key])) {
                $groupedModels[$key] = [
                    'model_type' => $modelType,
                    'action' => $action,
                    'models' => [],
                ];
            }

            $groupedModels[$key]['models'][] = $model;
        }

        return $groupedModels;
    }

    /**
     * 构建批量同步日志.
     */
    protected function buildBatchSyncLogs(
        array $groupedModels,
        array $targetNodes,
        string $sourceNode
    ): array {
        $syncLogs = [];

        foreach ($targetNodes as $targetNode) {
            foreach ($groupedModels as $group) {
                foreach ($group['models'] as $model) {
                    $syncHash = $this->generateSyncHash($model, $group['action']);

                    if (! $this->shouldCreateSyncLog(
                        $group['model_type'],
                        $model->id,
                        $group['action'],
                        $targetNode,
                        $syncHash
                    )) {
                        continue;
                    }

                    $syncLogs[] = [
                        'model_type' => $group['model_type'],
                        'model_id' => $model->id,
                        'action' => $group['action'],
                        'source_node' => $sourceNode,
                        'target_node' => $targetNode,
                        'status' => 'pending',
                        'payload' => $this->preparePayload($model, $group['action']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return $syncLogs;
    }

    /**
     * 批量插入同步日志.
     */
    protected function insertBatchSyncLogs(array $syncLogs): void
    {
        if (empty($syncLogs)) {
            return;
        }

        // 确保 payload 正确序列化（SyncLog 的 payload 是 array cast）
        foreach ($syncLogs as &$log) {
            if (isset($log['payload']) && is_array($log['payload'])) {
                $log['payload'] = json_encode($log['payload'], JSON_UNESCAPED_UNICODE);
            }
        }
        unset($log);

        $batchSize = config('sync.batch_size', 100);
        foreach (array_chunk($syncLogs, $batchSize) as $chunk) {
            SyncLog::insert($chunk);
        }
    }

    /**
     * 获取远程节点配置.
     */
    protected function getRemoteNodeConfig(string $targetNode): array
    {
        $config = config('sync');
        $remoteConfig = $config['remote_nodes'][$targetNode] ?? null;

        if (! $remoteConfig || ! $remoteConfig['api_key']) {
            throw new \Exception("远程节点配置不存在或未设置 API Key: {$targetNode}");
        }

        return $remoteConfig;
    }

    /**
     * 发送批量同步请求
     */
    protected function sendBatchSyncRequest(array $batchData, array $remoteConfig)
    {
        return Http::timeout($remoteConfig['timeout'])
            ->withHeaders([
                'Authorization' => 'Bearer '.$remoteConfig['api_key'],
                'Content-Type' => 'application/json',
                'X-Sync-Source-Node' => config('sync.node'),
            ])
            ->post($remoteConfig['url'].'/api/sync/receive-batch', [
                'batch' => $batchData,
                'source_node' => config('sync.node'),
                'timestamp' => now()->toIso8601String(),
            ]);
    }

    /**
     * 处理批量同步结果.
     */
    protected function handleBatchSyncResult(
        \Illuminate\Support\Collection $syncLogs,
        array $result,
        string $targetNode
    ): array {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        // 创建 sync_log_id 到 syncLog 的映射
        $syncLogMap = $syncLogs->keyBy('id');

        // 处理返回结果
        if (isset($result['results']) && is_array($result['results'])) {
            foreach ($result['results'] as $itemResult) {
                $syncLogId = $itemResult['sync_log_id'] ?? null;
                $syncLog = $syncLogId ? $syncLogMap->get($syncLogId) : null;

                if ($itemResult['success'] ?? false) {
                    ++$successCount;
                    if ($syncLog) {
                        $this->handleSuccessfulSync($syncLog);
                    }
                } else {
                    ++$failedCount;
                    $errorMsg = $itemResult['error'] ?? '未知错误';
                    $errors[] = $errorMsg;

                    if ($syncLog) {
                        $this->handleSyncFailure($syncLog, new \Exception($errorMsg));
                    }
                }
            }
        } else {
            // 如果没有详细结果，假设全部成功
            $successCount = $syncLogs->count();
            $syncLogs->each(function ($syncLog) {
                $this->handleSuccessfulSync($syncLog);
            });
        }

        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors,
        ];
    }

    /**
     * 处理成功同步.
     *
     * 优化：对于已删除的记录，直接标记完成，无需查找模型
     */
    protected function handleSuccessfulSync(SyncLog $syncLog): void
    {
        $syncLog->markAsCompleted();

        // 对于删除操作，不需要更新同步状态（记录已不存在）
        if ($syncLog->action === 'deleted') {
            return;
        }

        // 对于创建/更新操作，更新同步状态
        $model = $syncLog->model_type::find($syncLog->model_id);
        if ($model) {
            $syncHash = $this->generateSyncHash($model, $syncLog->action);
            SyncStatus::updateSyncStatus(
                $syncLog->model_type,
                $syncLog->model_id,
                $syncLog->target_node,
                $syncHash
            );
        }
    }

    /**
     * 处理同步失败.
     */
    protected function handleSyncFailure(SyncLog $syncLog, \Exception $e): void
    {
        Log::error('同步失败', [
            'sync_log_id' => $syncLog->id,
            'error' => $e->getMessage(),
        ]);

        $syncLog->markAsFailed($e->getMessage());
    }

    /**
     * 验证同步数据.
     */
    protected function validateSyncData(array $data): void
    {
        $modelType = $data['model_type'] ?? null;

        if (! $modelType || ! in_array($modelType, config('sync.sync_models'))) {
            throw new \Exception("模型不在同步列表中: {$modelType}");
        }
    }

    /**
     * 判断是否应该跳过同步（基于时间戳）.
     *
     * 优化：由于使用雪花ID，可以直接通过ID查找，无需通过唯一字段查找
     */
    protected function shouldSkipSync(array $data): bool
    {
        $modelType = $data['model_type'];
        $modelId = $data['model_id'];
        $timestamp = $data['timestamp'] ?? now()->toIso8601String();

        // 直接通过雪花ID查找（全局唯一，快速准确）
        $existingModel = $modelType::find($modelId);
        if ($existingModel && $existingModel->updated_at) {
            $remoteTimestamp = Carbon::parse($timestamp);
            // 如果本地数据更新，忽略远程同步（以最新为准）
            if ($existingModel->updated_at->gt($remoteTimestamp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 处理同步操作.
     */
    protected function processSyncAction(array $data): void
    {
        $modelType = $data['model_type'];
        $modelId = $data['model_id'];
        $action = $data['action'];
        $payload = $data['payload'];

        switch ($action) {
            case 'created':
            case 'updated':
                $model = $this->createOrUpdateModel($modelType, $modelId, $payload);

                if ($model === null) {
                    Log::warning('跳过同步记录处理：模型创建/更新失败', [
                        'model_type' => $modelType,
                        'model_id' => $modelId,
                        'action' => $action,
                    ]);
                    break;
                }

                if ($model instanceof \App\Models\Media) {
                    $this->downloadAndSaveMediaFile($model, $payload);
                }
                break;
            case 'deleted':
                $this->deleteModel($modelType, $modelId);
                break;
        }
    }

    /**
     * 规范化payload中的时间戳.
     */
    protected function normalizeTimestampsInPayload(array &$payload, Model $model): void
    {
        if (isset($payload['created_at']) && $model->created_at) {
            $payload['created_at'] = is_string($model->created_at)
                ? $model->created_at
                : $model->created_at->toIso8601String();
        }

        if (isset($payload['updated_at']) && $model->updated_at) {
            $payload['updated_at'] = is_string($model->updated_at)
                ? $model->updated_at
                : $model->updated_at->toIso8601String();
        }
    }

    /**
     * 添加媒体文件信息到payload.
     */
    protected function addMediaFileInfo(array &$payload, Model $model): void
    {
        if ($model instanceof \App\Models\Media) {
            $payload['file_url'] = $model->getUrl();
            $payload['file_path'] = $model->getPath();
            $payload['file_disk'] = $model->disk;
            // 添加 original_url，用于同步时下载文件
            $payload['original_url'] = $model->getUrl();
        }
    }

    /**
     * 清理模型payload（移除非数据库字段）.
     */
    protected function cleanPayloadForModel(string $modelType, array $payload): array
    {
        if ($modelType === \App\Models\Media::class) {
            return $this->cleanMediaPayload($payload);
        }

        return $this->cleanRegularModelPayload($modelType, $payload);
    }

    /**
     * 清理Media模型的payload.
     * 
     * 只移除计算字段（非数据库字段），保留所有数据库字段。
     * 因为使用雪花ID且同步接近实时，可以直接原样插入。
     */
    protected function cleanMediaPayload(array $payload): array
    {
        // 只移除计算字段（这些是 preparePayload 中添加的，不是数据库字段）
        $fieldsToRemove = ['file_url', 'file_path', 'file_disk', 'original_url', 'preview_url'];
        
        return array_diff_key($payload, array_flip($fieldsToRemove));
    }

    /**
     * 清理常规模型的payload.
     * 
     * 只移除明显不是数据库字段的字段（如关系数据）。
     * Laravel 的 create/update 会自动过滤非 fillable 字段，所以可以原样传递。
     * 因为使用雪花ID且同步接近实时，可以直接原样插入。
     */
    protected function cleanRegularModelPayload(string $modelType, array $payload): array
    {
        // 移除关系数据（toArray 默认不包含关系，但为了安全起见还是检查）
        // 关系数据通常是数组或对象，且字段名通常是关系方法名（如 product_translations）
        // 这里只移除明显的关系数据，保留所有可能的数据库字段
        
        // 实际上，如果 toArray() 没有加载关系，就不会有关系数据
        // 所以这里基本不需要清理，直接返回即可
        // Laravel 的 create/update 会自动处理非 fillable 字段
        
        return $payload;
    }

    /**
     * 更新现有模型（直接覆盖）.
     */
    protected function updateExistingModel(Model $model, array $cleanPayload): Model
    {
        $this->parseTimestampsInPayload($cleanPayload);
        $model->update($cleanPayload);

        return $model;
    }

    /**
     * 创建新模型（优化版：利用雪花ID全局唯一性，同步接近实时）.
     *
     * 优化点：
     * - 使用雪花ID，ID全局唯一，不会冲突
     * - 同步接近实时，唯一字段冲突（sku、slug）不会发生
     * - 直接创建，无需处理冲突
     */
    protected function createNewModel(string $modelType, int $modelId, array $cleanPayload): ?Model
    {
        // 设置雪花ID（全局唯一）
        $cleanPayload['id'] = $modelId;
        $this->parseTimestampsInPayload($cleanPayload);

        try {
            return $modelType::create($cleanPayload);
        } catch (\Exception $e) {
            // 如果创建失败（理论上不应该发生），记录日志
            Log::warning('创建同步记录失败', [
                'model_type' => $modelType,
                'model_id' => $modelId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 解析payload中的时间戳为Carbon实例.
     */
    protected function parseTimestampsInPayload(array &$payload): void
    {
        if (isset($payload['updated_at'])) {
            $payload['updated_at'] = Carbon::parse($payload['updated_at']);
        }
        if (isset($payload['created_at'])) {
            $payload['created_at'] = Carbon::parse($payload['created_at']);
        }
    }


    /**
     * 记录警告日志并返回null.
     */
    protected function logAndReturnNull(
        string $modelType,
        int $modelId,
        array $cleanPayload,
        \Exception $e
    ): ?Model {
        Log::warning('创建同步记录失败：可能缺少必填字段', [
            'model_type' => $modelType,
            'model_id' => $modelId,
            'clean_payload' => $cleanPayload,
            'error' => $e->getMessage(),
        ]);

        return null;
    }

    /**
     * 下载主媒体文件（带重试机制）.
     */
    protected function downloadMainMediaFile(
        \App\Models\Media $media,
        string $downloadUrl
    ): void {
        $timeout = config('sync.media_download_timeout', 900); // 默认15分钟
        $maxRetries = 3;
        $retryDelay = 2; // 秒
        
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info('开始下载 Media 文件', [
                    'media_id' => $media->id,
                    'url' => $downloadUrl,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                ]);

                $response = Http::timeout($timeout)
                    ->withHeaders([
                        'User-Agent' => 'Teanary-Sync-Client/1.0',
                        'Accept' => '*/*',
                        'Accept-Encoding' => 'gzip, deflate',
                    ])
                    ->retry(2, 1000) // HTTP 客户端级别的重试
                    ->get($downloadUrl);

                if (! $response->successful()) {
                    $errorBody = substr($response->body(), 0, 500); // 限制错误信息长度
                    throw new \Exception('下载文件失败: HTTP '.$response->status().($errorBody ? " - {$errorBody}" : ''));
                }

                $fileContent = $response->body();
                
                // 验证文件内容不为空
                if (empty($fileContent)) {
                    throw new \Exception('下载的文件内容为空');
                }
                
                // 验证是否为有效的图片（如果是图片类型）
                if (str_starts_with($media->mime_type ?? '', 'image/')) {
                    $imageInfo = @getimagesizefromstring($fileContent);
                    if ($imageInfo === false) {
                        throw new \Exception('下载的内容不是有效的图片文件');
                    }
                }

                // 保存文件
                $this->saveMediaFile($media, $fileContent);
                
                Log::info('Media 文件下载成功', [
                    'media_id' => $media->id,
                    'file_size' => strlen($fileContent),
                    'attempt' => $attempt,
                ]);
                
                return; // 成功，退出重试循环
                
            } catch (\Exception $e) {
                $lastException = $e;
                
                Log::warning('Media 文件下载失败（尝试 '.$attempt.'/'.$maxRetries.'）', [
                    'media_id' => $media->id,
                    'url' => $downloadUrl,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);
                
                // 如果不是最后一次尝试，等待后重试
                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                    $retryDelay *= 2; // 指数退避
                }
            }
        }
        
        // 所有重试都失败
        Log::error('Media 文件下载最终失败', [
            'media_id' => $media->id,
            'url' => $downloadUrl,
            'max_retries' => $maxRetries,
            'error' => $lastException?->getMessage(),
        ]);
        
        throw new \Exception('下载文件失败（已重试 '.$maxRetries.' 次）: '.($lastException?->getMessage() ?? '未知错误'));
    }

    /**
     * 保存媒体文件到磁盘.
     */
    protected function saveMediaFile(
        \App\Models\Media $media,
        string $fileContent
    ): void {
        $disk = $media->disk ?? config('media-library.disk_name', 'public');
        $diskInstance = \Illuminate\Support\Facades\Storage::disk($disk);
        $filePath = $this->getMediaFilePath($media);
        $directory = dirname($filePath);

        // 确保目录存在
        if (! $diskInstance->exists($directory)) {
            $diskInstance->makeDirectory($directory, 0755, true);
        }

        // 保存文件
        $diskInstance->put($filePath, $fileContent);
        
        // 验证文件是否成功保存
        if (! $diskInstance->exists($filePath)) {
            throw new \Exception('文件保存失败：文件不存在于磁盘');
        }
    }

    /**
     * 获取 Media 文件的完整路径（相对路径，用于 Storage）。
     * 
     * 注意：需要确保返回的是相对路径，而不是绝对路径。
     * 使用 PathGeneratorFactory 来获取正确的 PathGenerator。
     */
    protected function getMediaFilePath(\App\Models\Media $media): string
    {
        $fileName = $media->file_name ?? $media->name ?? 'file';
        
        // 使用 PathGeneratorFactory 获取正确的 PathGenerator
        $pathGeneratorFactory = app(\Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory::class);
        $pathGenerator = $pathGeneratorFactory->create($media);
        
        // 使用 PathGenerator 获取目录路径（相对路径）
        $directory = $pathGenerator->getPath($media);
        
        // 拼接文件名，返回相对路径
        return rtrim($directory, '/').'/'.$fileName;
    }

    /**
     * 检查 Media 文件是否已存在.
     */
    protected function mediaFileExists(\App\Models\Media $media): bool
    {
        $disk = $media->disk ?? config('media-library.disk_name', 'public');
        $diskInstance = \Illuminate\Support\Facades\Storage::disk($disk);
        $filePath = $this->getMediaFilePath($media);
        
        return $diskInstance->exists($filePath);
    }

    /**
     * 获取 Media 文件大小.
     */
    protected function getMediaFileSize(\App\Models\Media $media): ?int
    {
        $disk = $media->disk ?? config('media-library.disk_name', 'public');
        $diskInstance = \Illuminate\Support\Facades\Storage::disk($disk);
        $filePath = $this->getMediaFilePath($media);
        
        if ($diskInstance->exists($filePath)) {
            return $diskInstance->size($filePath);
        }
        
        return null;
    }

    /**
     * 触发媒体转换生成.
     */
    protected function triggerMediaConversions(
        \App\Models\Media $media
    ): void {
        try {
            // 刷新 Media 模型以确保数据是最新的
            $media->refresh();
            
            // 从 Media 的 model_type 和 model_id 字段获取关联的 model
            // 而不是依赖关联加载（因为同步时可能关联没有正确设置）
            $modelType = $media->model_type;
            $modelId = $media->model_id;
            
            if (! $modelType || ! $modelId) {
                Log::warning('Media 转换跳过：缺少 model_type 或 model_id', [
                    'media_id' => $media->id,
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                ]);
                return;
            }
            
            // 直接通过 model_type 和 model_id 查找 model
            if (! class_exists($modelType)) {
                Log::warning('Media 转换跳过：model_type 类不存在', [
                    'media_id' => $media->id,
                    'model_type' => $modelType,
                ]);
                return;
            }
            
            $model = $modelType::find($modelId);
            if (! $model) {
                Log::warning('Media 转换跳过：关联的 model 不存在', [
                    'media_id' => $media->id,
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                ]);
                return;
            }

            // 检查 model 是否有 registerMediaConversions 方法
            if (! method_exists($model, 'registerMediaConversions')) {
                Log::info('Media 转换跳过：model 没有 registerMediaConversions 方法', [
                    'media_id' => $media->id,
                    'model_type' => get_class($model),
                ]);
                return;
            }

            // 使用 Spatie Media Library 的 PerformConversionsJob 来生成 conversions
            // 从配置中获取 job class
            $jobClass = config('media-library.jobs.perform_conversions');
            if (! $jobClass || ! class_exists($jobClass)) {
                // 如果配置中没有，尝试使用默认的类名
                $jobClass = \Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob::class;
            }
            
            if (class_exists($jobClass)) {
                $queueConnection = config('media-library.queue_connection_name');
                $queueName = config('media-library.queue_name', 'default');
                
                // 分发 conversion job 到队列
                dispatch(new $jobClass($media))
                    ->onConnection($queueConnection ?: config('queue.default'))
                    ->onQueue($queueName);
                
                Log::info('已分发 Media 转换任务到队列', [
                    'media_id' => $media->id,
                    'model_type' => get_class($model),
                    'queue' => $queueName,
                    'queue_connection' => $queueConnection,
                ]);
            } else {
                // 如果 job class 不存在，尝试直接调用 performConversions（如果存在）
                if (method_exists($media, 'performConversions')) {
                    $media->performConversions();
                    Log::info('已触发 Media 转换生成（同步）', [
                        'media_id' => $media->id,
                    ]);
                } else {
                    Log::warning('Media 转换跳过：无法找到 PerformConversionsJob 或 performConversions 方法', [
                        'media_id' => $media->id,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('触发 Media 转换生成失败', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * 分发图片调整任务
     */
    protected function dispatchImageResizeJob(
        \App\Models\Media $media
    ): void {
        try {
            ResizeUploadedImage::dispatch($media)->onQueue('low');
        } catch (\Exception $e) {
            Log::warning('触发图片调整任务失败', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
