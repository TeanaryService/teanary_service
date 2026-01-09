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
        
        // 对于翻译模型，检查关键字段是否为空，如果为空则跳过同步
        if ($this->isTranslationModel($modelType)) {
            if ($action !== 'deleted' && ! $this->hasRequiredTranslationFields($modelType, $payload)) {
                // 关键字段为空，跳过同步
                return;
            }
        }
        
        $syncHash = $this->generateSyncHash($model, $action);

        // 检查是否是 Pivot 表（无主键）
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        
        // 对于 Pivot 表，使用 payload 的哈希值作为 model_id（因为 Pivot 表没有主键）
        // 对于普通模型，使用 model->id
        $modelId = $isPivot ? $this->generatePivotModelId($payload) : $model->id;

        foreach ($targetNodes as $targetNode) {
            if (! $this->shouldCreateSyncLog($modelType, $modelId, $action, $targetNode, $syncHash)) {
                continue;
            }

            $this->createSyncLog($modelType, $modelId, $action, $sourceNode, $targetNode, $payload);
        }
    }

    /**
     * 为 Pivot 表生成 model_id（使用 payload 的哈希值）.
     */
    protected function generatePivotModelId(array $payload): int
    {
        // 对 payload 进行排序，确保相同的数据生成相同的哈希
        ksort($payload);
        $hash = md5(json_encode($payload, JSON_UNESCAPED_UNICODE));
        // 将哈希值的前 15 位转换为整数（避免超出 bigint 范围）
        return (int) hexdec(substr($hash, 0, 15));
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

        // 对模型类型进行排序：Media 类型放在最后处理
        // 这样可以确保 Media 关联的 model 已经同步完成
        uksort($groupedByModelType, function ($a, $b) {
            $isMediaA = $a === \App\Models\Media::class;
            $isMediaB = $b === \App\Models\Media::class;
            
            // Media 类型排在最后
            if ($isMediaA && ! $isMediaB) {
                return 1;
            }
            if (! $isMediaA && $isMediaB) {
                return -1;
            }
            
            // 其他类型保持原有顺序
            return 0;
        });

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
     * 检查是否是翻译模型.
     */
    protected function isTranslationModel(string $modelType): bool
    {
        $translationModels = [
            \App\Models\ProductTranslation::class,
            \App\Models\CategoryTranslation::class,
            \App\Models\AttributeTranslation::class,
            \App\Models\AttributeValueTranslation::class,
            \App\Models\SpecificationTranslation::class,
            \App\Models\SpecificationValueTranslation::class,
            \App\Models\PromotionTranslation::class,
            \App\Models\ArticleTranslation::class,
            \App\Models\CountryTranslation::class,
            \App\Models\ZoneTranslation::class,
            \App\Models\UserGroupTranslation::class,
        ];

        return in_array($modelType, $translationModels);
    }

    /**
     * 检查翻译模型的关键字段是否为空.
     * 
     * 如果关键字段为空，说明这是无效的翻译记录，不应该同步.
     * 
     * @param string $modelType 模型类型
     * @param array $payload 同步数据
     * @return bool 如果关键字段至少有一个非空，返回 true；否则返回 false
     */
    protected function hasRequiredTranslationFields(string $modelType, array $payload): bool
    {
        // 定义每个翻译模型的关键字段（必填字段）
        $requiredFields = [
            \App\Models\ProductTranslation::class => ['name'],
            \App\Models\CategoryTranslation::class => ['name'],
            \App\Models\AttributeTranslation::class => ['name'],
            \App\Models\AttributeValueTranslation::class => ['name'],
            \App\Models\SpecificationTranslation::class => ['name'],
            \App\Models\SpecificationValueTranslation::class => ['name'],
            \App\Models\PromotionTranslation::class => ['name'],
            \App\Models\ArticleTranslation::class => ['title'],
            \App\Models\CountryTranslation::class => ['name'],
            \App\Models\ZoneTranslation::class => ['name'],
            \App\Models\UserGroupTranslation::class => ['name'],
        ];

        $fields = $requiredFields[$modelType] ?? [];
        
        if (empty($fields)) {
            // 如果没有定义关键字段，默认允许同步
            return true;
        }

        // 检查所有关键字段是否至少有一个非空
        foreach ($fields as $field) {
            $value = $payload[$field] ?? null;
            if (! empty($value)) {
                return true;
            }
        }

        // 所有关键字段都为空，跳过同步
        return false;
    }

    /**
     * 准备同步数据.
     */
    protected function preparePayload(Model $model, string $action): array
    {
        // 检查是否是 Pivot 表（无主键）
        $isPivot = is_subclass_of(get_class($model), \Illuminate\Database\Eloquent\Relations\Pivot::class);
        
        if ($action === 'deleted') {
            if ($isPivot) {
                // Pivot 表删除时，返回所有字段（用于复合键查找）
                return $model->toArray();
            }
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
     * 创建或更新模型.
     * 
     * 原样写入源节点数据，不过滤任何字段（包括id等）.
     * 强制使用源节点的 ID，确保不会重新生成 ID.
     * 对于 Media 模型，移除非数据库字段（如 original_url, preview_url）.
     * 对于 Pivot 表（无主键），使用复合键查找和更新.
     */
    protected function createOrUpdateModel(string $modelType, int $modelId, array $payload): ?Model
    {
        // 对于 Media 模型，移除非数据库字段
        if ($modelType === \App\Models\Media::class) {
            $payload = $this->cleanMediaPayload($payload);
        }

        // 检查是否是 Pivot 表（无主键）
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        
        if ($isPivot) {
            // Pivot 表使用复合键，直接使用 updateOrCreate
            return $this->createOrUpdatePivotModel($modelType, $payload);
        }

        // 强制使用源节点的 ID（确保不会被重新生成）
        // 必须在处理时间戳之前设置，确保 id 字段存在
        $payload['id'] = $modelId;

        // 只处理时间戳格式转换
        $this->parseTimestampsInPayload($payload);

        // 直接通过雪花ID查找（全局唯一）
        $model = $modelType::find($modelId);

        if ($model) {
            // 对于翻译模型，如果关键字段为空，则删除该记录而不是更新
            if ($this->isTranslationModel($modelType) && ! $this->hasRequiredTranslationFields($modelType, $payload)) {
                // 关键字段为空，删除该记录
                $model->withoutEvents(function () use ($model) {
                    $model->delete();
                });
                return null;
            }
            
            // 模型已存在，直接更新覆盖（原样数据，但确保使用源节点的 id）
            // 移除 id 字段，因为 update 不应该更新主键
            $updatePayload = $payload;
            unset($updatePayload['id']);
            
            // 对于 Media 模型，使用 withoutEvents 禁用所有事件（包括 Observer）
            // 防止更新时触发同步导致死循环
            if ($modelType === \App\Models\Media::class) {
                $model->withoutEvents(function () use ($model, $updatePayload) {
                    $model->update($updatePayload);
                });
            } else {
                $model->update($updatePayload);
            }
            
            return $model;
        }

        // 对于翻译模型，如果关键字段为空，则不创建记录
        if ($this->isTranslationModel($modelType) && ! $this->hasRequiredTranslationFields($modelType, $payload)) {
            // 关键字段为空，不创建记录
            return null;
        }

        // 模型不存在，创建新记录
        // 使用 withoutEvents 禁用所有模型事件（包括 HasSnowflakeId 的 creating 事件）
        // 然后使用 fill 设置属性，确保使用源节点的 id
        try {
            return $modelType::withoutEvents(function () use ($modelType, $modelId, $payload) {
                $model = new $modelType();
                $model->fill($payload);
                // 强制确保使用源节点的 id（fill 之后再次设置，确保不被覆盖）
                $model->id = $modelId;
                $model->save();
                return $model;
            });
        } catch (\Exception $e) {
            Log::warning('创建同步记录失败', [
                'model_type' => $modelType,
                'model_id' => $modelId,
                'error' => $e->getMessage(),
                'payload_keys' => array_keys($payload),
            ]);
            return null;
        }
    }

    /**
     * 创建或更新 Pivot 表模型（无主键）.
     * 
     * Pivot 表使用复合键，通过所有字段组合来唯一标识记录.
     */
    protected function createOrUpdatePivotModel(string $modelType, array $payload): ?Model
    {
        try {
            // Pivot 表使用 updateOrCreate，通过所有 fillable 字段组合来查找
            // 如果存在则更新，不存在则创建
            return $modelType::updateOrCreate(
                $payload, // 使用所有字段作为查找条件
                $payload  // 如果不存在，使用相同数据创建
            );
        } catch (\Exception $e) {
            Log::warning('创建/更新 Pivot 表记录失败', [
                'model_type' => $modelType,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return null;
        }
    }

    /**
     * 清理 Media payload，移除非数据库字段.
     * 
     * 根据迁移文件，Media 表的字段包括：
     * id, model_type, model_id, uuid, collection_name, name, file_name,
     * mime_type, disk, conversions_disk, size, manipulations,
     * custom_properties, generated_conversions, responsive_images,
     * order_column, created_at, updated_at
     */
    protected function cleanMediaPayload(array $payload): array
    {
        // 移除非数据库字段（这些是 preparePayload 中添加的计算字段）
        $fieldsToRemove = ['original_url', 'preview_url', 'file_url', 'file_path', 'file_disk'];
        
        return array_diff_key($payload, array_flip($fieldsToRemove));
    }

    /**
     * 下载并保存 Media 文件，并生成缩略图.
     * 
     * 注意：此方法在同步接收过程中调用，需要禁用 Media 同步以避免死循环
     */
    protected function downloadAndSaveMediaFile(
        \App\Models\Media $media,
        array $payload
    ): void {
        // 确保 Media 同步被禁用（防止文件下载和转换过程中触发同步导致死循环）
        $wasDisabled = \App\Observers\MediaObserver::$syncDisabled;
        \App\Observers\MediaObserver::$syncDisabled = true;
        
        try {
            // 获取下载 URL
            $downloadUrl = $payload['original_url'] ?? $payload['file_url'] ?? null;
            
            if (! $downloadUrl) {
                Log::warning('Media 同步数据缺少下载 URL', [
                    'media_id' => $media->id,
                    'payload_keys' => array_keys($payload),
                ]);
                return;
            }

            // 检查文件是否已存在
            if ($this->mediaFileExists($media)) {
                Log::info('Media 文件已存在，跳过下载', [
                    'media_id' => $media->id,
                    'file_path' => $this->getMediaFilePath($media),
                ]);
                // 文件已存在，只需触发 conversions（如果还没有生成）
                $this->triggerMediaConversions($media);
                return;
            }

            // 下载并保存文件
            try {
                $this->downloadAndSaveFile($media, $downloadUrl);
                
                // 文件保存成功后，触发 conversions 生成缩略图
                $this->triggerMediaConversions($media);

                Log::info('Media 文件同步成功', [
                    'media_id' => $media->id,
                    'file_size' => $this->getMediaFileSize($media),
                ]);
            } catch (\Exception $e) {
                Log::error('Media 文件同步失败', [
                    'media_id' => $media->id,
                    'url' => $downloadUrl,
                    'error' => $e->getMessage(),
                ]);
                // 不抛出异常，避免影响其他数据的同步
            }
        } finally {
            // 恢复之前的同步状态
            \App\Observers\MediaObserver::$syncDisabled = $wasDisabled;
        }
    }

    /**
     * 删除模型.
     * 
     * 对于 Pivot 表（无主键），使用复合键删除.
     */
    protected function deleteModel(string $modelType, int $modelId, array $payload = []): void
    {
        // 检查是否是 Pivot 表（无主键）
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        
        if ($isPivot) {
            // Pivot 表使用复合键删除
            try {
                // 对于 Pivot 表，payload 应该包含所有 fillable 字段
                // 如果 payload 不完整，尝试使用所有 fillable 字段构建查询条件
                $modelInstance = new $modelType();
                $fillableFields = $modelInstance->getFillable();
                
                // 构建查询条件：只使用 payload 中存在的 fillable 字段
                $whereConditions = [];
                foreach ($fillableFields as $field) {
                    if (isset($payload[$field])) {
                        $whereConditions[$field] = $payload[$field];
                    }
                }
                
                // 如果没有任何条件，记录警告并返回
                if (empty($whereConditions)) {
                    Log::warning('删除 Pivot 表记录失败：payload 中缺少必要的字段', [
                        'model_type' => $modelType,
                        'payload' => $payload,
                        'fillable_fields' => $fillableFields,
                    ]);
                    return;
                }
                
                // 使用构建的条件查找记录
                $query = $modelType::query();
                foreach ($whereConditions as $field => $value) {
                    $query->where($field, $value);
                }
                $models = $query->get();
                
                if ($models->isEmpty()) {
                    Log::warning('删除 Pivot 表记录：未找到匹配的记录', [
                        'model_type' => $modelType,
                        'where_conditions' => $whereConditions,
                        'payload' => $payload,
                    ]);
                } elseif ($models->count() === 1) {
                    // 只有一条记录，直接删除
                    $models->first()->delete();
                    Log::debug('删除 Pivot 表记录成功', [
                        'model_type' => $modelType,
                        'where_conditions' => $whereConditions,
                    ]);
                } else {
                    // 多条记录匹配，记录警告但删除所有匹配的记录
                    Log::warning('删除 Pivot 表记录：找到多条匹配记录，将删除所有匹配的记录', [
                        'model_type' => $modelType,
                        'where_conditions' => $whereConditions,
                        'count' => $models->count(),
                        'payload' => $payload,
                    ]);
                    foreach ($models as $model) {
                        $model->delete();
                    }
                }
            } catch (\Exception $e) {
                Log::warning('删除 Pivot 表记录失败', [
                    'model_type' => $modelType,
                    'payload' => $payload,
                    'error' => $e->getMessage(),
                ]);
            }
            return;
        }

        // 普通模型通过 ID 删除
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

        // 检查是否是 Pivot 表（无主键）
        $isPivot = is_subclass_of($syncLog->model_type, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        
        if ($isPivot) {
            // Pivot 表不需要更新同步状态（没有主键，无法通过 ID 查找）
            // 同步状态主要用于避免重复同步，Pivot 表通过复合键已经可以避免重复
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
     * 对于 Pivot 表（无主键），使用复合键查找
     */
    protected function shouldSkipSync(array $data): bool
    {
        $modelType = $data['model_type'];
        $modelId = $data['model_id'];
        $timestamp = $data['timestamp'] ?? now()->toIso8601String();
        $payload = $data['payload'] ?? [];

        // 检查是否是 Pivot 表（无主键）
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        
        if ($isPivot) {
            // Pivot 表使用复合键查找
            try {
                $existingModel = $modelType::where($payload)->first();
                // Pivot 表通常没有时间戳，所以不检查时间戳
                // 如果记录已存在，就跳过（避免重复）
                return $existingModel !== null;
            } catch (\Exception $e) {
                // 如果查找失败，不跳过，继续同步
                return false;
            }
        }

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

                // 如果是 Media 模型，下载文件并生成缩略图
                if ($model instanceof \App\Models\Media) {
                    $this->downloadAndSaveMediaFile($model, $payload);
                }
                break;
            case 'deleted':
                $this->deleteModel($modelType, $modelId, $payload);
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
     * 
     * 添加 original_url 用于同步时下载文件
     */
    protected function addMediaFileInfo(array &$payload, Model $model): void
    {
        if ($model instanceof \App\Models\Media) {
            // 添加 original_url，用于同步时下载文件
            $payload['original_url'] = $model->getUrl();
        }
    }

    // 已移除清理逻辑，原样写入数据
    // protected function cleanPayloadForModel(...) { ... }
    // protected function cleanMediaPayload(...) { ... }
    // protected function cleanRegularModelPayload(...) { ... }
    // protected function updateExistingModel(...) { ... }
    // protected function createNewModel(...) { ... }

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
     * 下载并保存文件（带重试机制）.
     */
    protected function downloadAndSaveFile(
        \App\Models\Media $media,
        string $downloadUrl
    ): void {
        $timeout = config('sync.media_download_timeout', 900);
        $maxRetries = 3;
        $retryDelay = 2;
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info('开始下载 Media 文件', [
                    'media_id' => $media->id,
                    'url' => $downloadUrl,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                ]);

                // 下载文件
                $response = Http::timeout($timeout)
                    ->withHeaders([
                        'User-Agent' => 'Teanary-Sync-Client/1.0',
                        'Accept' => '*/*',
                    ])
                    ->retry(2, 1000)
                    ->get($downloadUrl);

                if (! $response->successful()) {
                    throw new \Exception('HTTP '.$response->status());
                }

                $fileContent = $response->body();
                if (empty($fileContent)) {
                    throw new \Exception('文件内容为空');
                }
                
                // 验证图片文件
                if (str_starts_with($media->mime_type ?? '', 'image/')) {
                    if (@getimagesizefromstring($fileContent) === false) {
                        throw new \Exception('不是有效的图片文件');
                    }
                }

                // 保存文件
                $this->saveMediaFile($media, $fileContent);
                
                Log::info('Media 文件下载成功', [
                    'media_id' => $media->id,
                    'file_size' => strlen($fileContent),
                    'attempt' => $attempt,
                ]);
                
                return; // 成功
                
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning('Media 文件下载失败，准备重试', [
                    'media_id' => $media->id,
                    'url' => $downloadUrl,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                ]);
                
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
            'error' => $lastException?->getMessage() ?? '未知错误',
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
     * 获取 Media 文件的相对路径（用于 Storage）.
     */
    protected function getMediaFilePath(\App\Models\Media $media): string
    {
        $fileName = $media->file_name ?? $media->name ?? 'file';
        $pathGeneratorFactory = app(\Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory::class);
        $pathGenerator = $pathGeneratorFactory->create($media);
        $directory = rtrim($pathGenerator->getPath($media), '/');
        
        return $directory.'/'.$fileName;
    }

    /**
     * 检查 Media 文件是否已存在.
     */
    protected function mediaFileExists(\App\Models\Media $media): bool
    {
        $disk = $media->disk ?? config('media-library.disk_name', 'public');
        return \Illuminate\Support\Facades\Storage::disk($disk)->exists($this->getMediaFilePath($media));
    }

    /**
     * 获取 Media 文件大小.
     */
    protected function getMediaFileSize(\App\Models\Media $media): ?int
    {
        $disk = $media->disk ?? config('media-library.disk_name', 'public');
        $diskInstance = \Illuminate\Support\Facades\Storage::disk($disk);
        $filePath = $this->getMediaFilePath($media);
        
        return $diskInstance->exists($filePath) ? $diskInstance->size($filePath) : null;
    }

    /**
     * 触发媒体转换生成（使用 Spatie Media Library 标准方法）.
     * 
     * 注意：此方法在同步接收过程中调用，Media 同步应该已经被禁用
     * 但为了安全起见，这里再次确保同步被禁用
     */
    protected function triggerMediaConversions(
        \App\Models\Media $media
    ): void {
        // 确保 Media 同步被禁用（防止转换过程中触发同步导致死循环）
        $wasDisabled = \App\Observers\MediaObserver::$syncDisabled;
        \App\Observers\MediaObserver::$syncDisabled = true;
        
        try {
            $media->refresh();
            
            // 获取关联的 model
            $model = $this->getMediaModel($media);
            if (! $model) {
                // 已记录日志，model 可能还未同步
                return;
            }

            // 检查 model 是否实现了 HasMedia 接口
            if (! method_exists($model, 'registerMediaConversions')) {
                Log::debug('Media 转换跳过：model 没有 registerMediaConversions 方法', [
                    'media_id' => $media->id,
                    'model_type' => get_class($model),
                ]);
                return;
            }

            // 获取 conversions collection
            // 使用 ConversionCollection::createForMedia 创建 conversions collection
            $conversions = \Spatie\MediaLibrary\Conversions\ConversionCollection::createForMedia($media);
            
            if ($conversions->isEmpty()) {
                Log::debug('Media 转换跳过：没有定义的 conversions', [
                    'media_id' => $media->id,
                    'model_type' => get_class($model),
                ]);
                return;
            }
            
            // 使用自定义的 Job 包装器，在执行时禁用 Media 同步
            // 防止转换过程中更新 Media 模型时触发同步导致死循环
            $queueConnection = config('media-library.queue_connection_name');
            $queueName = config('media-library.queue_name', 'default');
            
            dispatch(new \App\Jobs\PerformMediaConversionsJob($conversions, $media))
                ->onConnection($queueConnection ?: config('queue.default'))
                ->onQueue($queueName);
            
            Log::info('已分发 Media 转换任务', [
                'media_id' => $media->id,
                'model_type' => get_class($model),
                'conversions_count' => $conversions->count(),
                'queue' => $queueName,
            ]);
            
        } catch (\Exception $e) {
            Log::error('触发 Media 转换失败', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            // 恢复之前的同步状态
            \App\Observers\MediaObserver::$syncDisabled = $wasDisabled;
        }
    }

    /**
     * 获取 Media 关联的 model.
     */
    protected function getMediaModel(\App\Models\Media $media): ?Model
    {
        $modelType = $media->model_type;
        $modelId = $media->model_id;
        
        if (! $modelType || ! $modelId) {
            Log::debug('Media 转换跳过：缺少 model_type 或 model_id', [
                'media_id' => $media->id,
                'model_type' => $modelType,
                'model_id' => $modelId,
            ]);
            return null;
        }
        
        if (! class_exists($modelType)) {
            Log::warning('Media 转换跳过：model_type 类不存在', [
                'media_id' => $media->id,
                'model_type' => $modelType,
            ]);
            return null;
        }
        
        $model = $modelType::find($modelId);
        if (! $model) {
            // 注意：这里使用 debug 级别，因为可能是同步顺序问题
            // model 可能稍后会同步，conversions 可以在后续手动触发
            Log::debug('Media 转换跳过：关联的 model 不存在（可能还未同步）', [
                'media_id' => $media->id,
                'model_type' => $modelType,
                'model_id' => $modelId,
            ]);
            return null;
        }
        
        return $model;
    }

    /**
     * 分发图片调整任务
     * 
     * 已移除，稍后重写
     */
    // protected function dispatchImageResizeJob(
    //     \App\Models\Media $media
    // ): void {
    //     // Media 同步功能已移除，稍后重写
    // }
}
