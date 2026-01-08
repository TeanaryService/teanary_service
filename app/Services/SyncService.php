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
 * 数据同步服务（已优化：利用雪花ID全局唯一性，移除外键约束）.
 *
 * 优化点：
 * 1. 由于使用雪花ID，ID是全局唯一的，可以直接通过ID查找，无需通过唯一字段查找
 * 2. 简化了冲突处理逻辑，ID冲突不会发生
 * 3. 移除了外键约束，外键约束不会失败，可以直接复制整行数据
 * 4. 可以直接复制整行数据，无需担心ID冲突和外键约束
 * 5. 批量同步更高效，可以直接批量插入/更新
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
     * 创建或更新模型（优化版：利用雪花ID全局唯一性）.
     *
     * 由于使用雪花ID，ID是全局唯一的，可以直接通过ID查找，简化逻辑
     */
    protected function createOrUpdateModel(string $modelType, int $modelId, array $payload): ?Model
    {
        $cleanPayload = $this->cleanPayloadForModel($modelType, $payload);

        // 直接通过雪花ID查找（全局唯一，无需担心冲突）
        $model = $modelType::find($modelId);

        if ($model) {
            // 模型已存在，直接更新
            return $this->updateExistingModel($model, $cleanPayload);
        }

        // 模型不存在，创建新记录（使用原始雪花ID）
        return $this->createNewModel($modelType, $modelId, $cleanPayload);
    }

    /**
     * 通过唯一字段查找模型（用于处理唯一字段冲突）.
     *
     * 注意：
     * - 由于使用雪花ID，ID冲突不会发生
     * - 由于移除了外键约束，外键约束不会失败
     * - 唯一字段冲突（如sku、slug）仍可能发生，需要处理
     */
    protected function findModelByUniqueFields(string $modelType, array $payload): ?Model
    {
        // 定义各模型的唯一字段映射（排除ID，因为ID是全局唯一的）
        $uniqueFieldsMap = [
            \App\Models\ProductVariant::class => ['sku'],
            \App\Models\Product::class => ['slug'],
            // 可以继续添加其他模型的唯一字段
        ];

        $uniqueFields = $uniqueFieldsMap[$modelType] ?? [];

        foreach ($uniqueFields as $field) {
            if (isset($payload[$field]) && $payload[$field] !== null) {
                $model = $modelType::where($field, $payload[$field])->first();
                if ($model) {
                    return $model;
                }
            }
        }

        return null;
    }

    /**
     * 下载并保存 Media 文件.
     */
    protected function downloadAndSaveMediaFile(
        \Spatie\MediaLibrary\MediaCollections\Models\Media $media,
        array $payload
    ): void {
        if (! isset($payload['original_url'])) {
            Log::warning('Media 同步数据缺少 original_url', [
                'media_id' => $media->id,
                'payload_keys' => array_keys($payload),
            ]);

            return;
        }

        try {
            $this->downloadMainMediaFile($media, $payload['original_url']);
            $this->triggerMediaConversions($media);
            $this->dispatchImageResizeJob($media);

            Log::info('Media 文件同步成功', [
                'media_id' => $media->id,
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
        if ($modelType === \Spatie\MediaLibrary\MediaCollections\Models\Media::class
            || $modelType === \App\Models\Media::class
            || is_subclass_of($modelType, \Spatie\MediaLibrary\MediaCollections\Models\Media::class)) {
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

        if ($modelType === \Spatie\MediaLibrary\MediaCollections\Models\Media::class
            || $modelType === \App\Models\Media::class
            || is_subclass_of($modelType, \Spatie\MediaLibrary\MediaCollections\Models\Media::class)) {
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

                if ($model instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
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
        if ($model instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            $payload['file_url'] = $model->getUrl();
            $payload['file_path'] = $model->getPath();
            $payload['file_disk'] = $model->disk;
        }
    }

    /**
     * 清理模型payload（移除非数据库字段）.
     */
    protected function cleanPayloadForModel(string $modelType, array $payload): array
    {
        if ($modelType === \Spatie\MediaLibrary\MediaCollections\Models\Media::class) {
            return $this->cleanMediaPayload($payload);
        }

        return $this->cleanRegularModelPayload($modelType, $payload);
    }

    /**
     * 清理Media模型的payload.
     */
    protected function cleanMediaPayload(array $payload): array
    {
        $mediaDbFields = [
            'id', 'model_type', 'model_id', 'uuid', 'collection_name', 'name', 'file_name',
            'mime_type', 'disk', 'conversions_disk', 'size', 'manipulations',
            'custom_properties', 'generated_conversions', 'responsive_images',
            'order_column', 'created_at', 'updated_at',
        ];

        $fieldsToRemove = ['file_url', 'file_path', 'file_disk', 'original_url', 'preview_url'];
        $cleanPayload = array_diff_key($payload, array_flip($fieldsToRemove));

        return array_filter($cleanPayload, function ($key) use ($mediaDbFields) {
            return in_array($key, $mediaDbFields);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * 清理常规模型的payload.
     */
    protected function cleanRegularModelPayload(string $modelType, array $payload): array
    {
        $modelInstance = new $modelType;
        $fillableFields = $modelInstance->getFillable();

        return array_filter($payload, function ($value, $key) use ($fillableFields) {
            if (in_array($key, ['created_at', 'updated_at', 'id'])) {
                return true;
            }

            if (! in_array($key, $fillableFields)) {
                return false;
            }

            if ($value === null) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * 更新现有模型.
     */
    protected function updateExistingModel(Model $model, array $cleanPayload): Model
    {
        $this->parseTimestampsInPayload($cleanPayload);

        if ($this->hasUpdateData($cleanPayload)) {
            $model->update($cleanPayload);
        }

        return $model;
    }

    /**
     * 创建新模型（优化版：利用雪花ID全局唯一性，移除外键约束）.
     *
     * 由于使用雪花ID且移除了外键约束：
     * - ID冲突不会发生（雪花ID全局唯一）
     * - 外键约束不会失败（已移除外键约束）
     * - 唯一字段冲突（如sku、slug）仍可能发生，需要处理
     */
    protected function createNewModel(string $modelType, int $modelId, array $cleanPayload): ?Model
    {
        // 设置雪花ID（全局唯一，不会冲突）
        $cleanPayload['id'] = $modelId;
        $this->parseTimestampsInPayload($cleanPayload);

        try {
            return $modelType::create($cleanPayload);
        } catch (\Illuminate\Database\QueryException $e) {
            // 唯一约束冲突（唯一字段如sku、slug等，非外键约束）
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'Duplicate entry')) {
                return $this->handleUniqueFieldConflict($modelType, $modelId, $cleanPayload, $e);
            }

            return $this->logAndReturnNull($modelType, $modelId, $cleanPayload, $e);
        } catch (\Exception $e) {
            return $this->logAndReturnNull($modelType, $modelId, $cleanPayload, $e);
        }
    }

    /**
     * 处理唯一字段冲突（非ID冲突，非外键约束）.
     *
     * 由于使用雪花ID且移除了外键约束：
     * - ID冲突不会发生（雪花ID全局唯一）
     * - 外键约束不会失败（已移除外键约束）
     * - 唯一字段冲突（如sku、slug）仍可能发生，需要处理
     *
     * 如果找到相同唯一字段的记录，更新该记录（保持原始雪花ID）
     */
    protected function handleUniqueFieldConflict(
        string $modelType,
        int $modelId,
        array $cleanPayload,
        \Exception $e
    ): ?Model {
        // 尝试通过唯一字段查找现有记录
        $model = $this->findModelByUniqueFields($modelType, $cleanPayload);

        if ($model) {
            // 找到相同唯一字段的记录，更新它（保持原始ID）
            // 注意：如果ID不同，说明是不同节点创建的相同唯一字段的记录
            // 这种情况下，我们更新现有记录，但保持其原始ID
            $this->parseTimestampsInPayload($cleanPayload);

            // 移除ID，避免更新ID（保持原始记录的ID）
            unset($cleanPayload['id']);

            if ($this->hasUpdateData($cleanPayload)) {
                $model->update($cleanPayload);
            }

            Log::info('通过唯一字段找到现有记录并更新', [
                'model_type' => $modelType,
                'existing_id' => $model->id,
                'sync_id' => $modelId,
                'unique_fields' => $this->getUniqueFields($modelType),
            ]);

            return $model;
        }

        // 如果找不到，可能是ID冲突（理论上不应该发生，但保留处理）
        // 尝试直接查找ID
        $existingModel = $modelType::find($modelId);
        if ($existingModel) {
            // ID已存在，直接更新
            return $this->updateExistingModel($existingModel, $cleanPayload);
        }

        Log::warning('创建同步记录失败：唯一约束冲突且无法找到记录', [
            'model_type' => $modelType,
            'model_id' => $modelId,
            'clean_payload' => $cleanPayload,
            'error' => $e->getMessage(),
        ]);

        return null;
    }

    /**
     * 获取模型的唯一字段列表（用于日志）.
     */
    protected function getUniqueFields(string $modelType): array
    {
        $uniqueFieldsMap = [
            \App\Models\ProductVariant::class => ['sku'],
            \App\Models\Product::class => ['slug'],
        ];

        return $uniqueFieldsMap[$modelType] ?? [];
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
     * 检查是否有需要更新的数据（排除时间戳和ID）.
     */
    protected function hasUpdateData(array $cleanPayload): bool
    {
        $updateData = array_diff_key($cleanPayload, array_flip(['created_at', 'updated_at', 'id']));

        return ! empty($updateData);
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
     * 下载主媒体文件.
     */
    protected function downloadMainMediaFile(
        \Spatie\MediaLibrary\MediaCollections\Models\Media $media,
        string $downloadUrl
    ): void {
        $timeout = config('sync.media_download_timeout', 900); // 默认15分钟
        $response = Http::timeout($timeout)
            ->withHeaders([
                'User-Agent' => 'Teanary-Sync-Client/1.0',
                'Accept' => '*/*',
            ])
            ->get($downloadUrl);

        if (! $response->successful()) {
            $errorBody = $response->body();
            Log::error('下载 Media 文件失败', [
                'media_id' => $media->id,
                'url' => $downloadUrl,
                'status' => $response->status(),
                'response' => $errorBody,
            ]);
            throw new \Exception('下载文件失败: HTTP '.$response->status().($errorBody ? " - {$errorBody}" : ''));
        }

        $this->saveMediaFile($media, $response->body());
    }

    /**
     * 保存媒体文件到磁盘.
     */
    protected function saveMediaFile(
        \Spatie\MediaLibrary\MediaCollections\Models\Media $media,
        string $fileContent
    ): void {
        $disk = $media->disk ?? config('media-library.disk_name', 'public');
        $diskInstance = \Illuminate\Support\Facades\Storage::disk($disk);
        $filePath = $media->getPath();
        $directory = dirname($filePath);

        if (! $diskInstance->exists($directory)) {
            $diskInstance->makeDirectory($directory, 0755, true);
        }

        $diskInstance->put($filePath, $fileContent);
    }

    /**
     * 触发媒体转换生成.
     */
    protected function triggerMediaConversions(
        \Spatie\MediaLibrary\MediaCollections\Models\Media $media
    ): void {
        try {
            $model = $media->model;
            if (! $model || ! method_exists($model, 'registerMediaConversions')) {
                return;
            }

            $media->refresh();

            if (method_exists($media, 'performConversions')) {
                $media->performConversions();
            } elseif (method_exists($media, 'performOnQueue')) {
                $media->performOnQueue();
            } else {
                $model->registerMediaConversions($media);
                Log::info('Media 转换将在首次访问时自动生成', [
                    'media_id' => $media->id,
                ]);
            }

            Log::info('已触发 Media 转换生成', [
                'media_id' => $media->id,
            ]);
        } catch (\Exception $e) {
            Log::warning('触发 Media 转换生成失败', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 分发图片调整任务
     */
    protected function dispatchImageResizeJob(
        \Spatie\MediaLibrary\MediaCollections\Models\Media $media
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
