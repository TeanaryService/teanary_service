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
 * 数据同步服务.
 * 
 * 基于雪花ID全局唯一性，将创建、更新、删除分开处理，逻辑更清晰.
 */
class SyncService
{
    /**
     * 记录需要同步的数据变更.
     * 
     * @param Model $model 发生变更的模型实例
     * @param string $action 操作类型：'created'|'updated'|'deleted'
     * @param string $sourceNode 源节点名称
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
        
        // 翻译模型：如果关键字段为空，跳过同步（避免无效数据）
        if ($this->isTranslationModel($modelType)) {
            if ($action !== 'deleted' && ! $this->hasRequiredTranslationFields($modelType, $payload)) {
                return;
            }
        }
        
        $syncHash = $this->generateSyncHash($model, $action);
        // Pivot 表没有主键，使用 payload 哈希值作为 model_id
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        $modelId = $isPivot ? $this->generatePivotModelId($payload) : $model->id;

        // 为每个目标节点创建同步日志
        foreach ($targetNodes as $targetNode) {
            if (! $this->shouldCreateSyncLog($modelType, $modelId, $action, $targetNode, $syncHash)) {
                continue;
            }

            $this->createSyncLog($modelType, $modelId, $action, $sourceNode, $targetNode, $payload);
        }
    }

    /**
     * 为 Pivot 表生成 model_id（使用 payload 的哈希值）.
     * 
     * Pivot 表没有主键，使用所有字段的哈希值作为唯一标识.
     * 只使用 fillable 字段，确保生成的 model_id 稳定且唯一.
     * 
     * @param array $payload 同步数据
     * @return int 生成的 model_id
     */
    protected function generatePivotModelId(array $payload): int
    {
        // 只保留 fillable 字段，移除其他字段（如 pivot 相关字段）
        // 注意：这里假设 payload 已经包含了所有必要的字段
        // 如果 payload 来自 preparePayload，它应该已经包含了所有字段
        
        // 对字段进行排序，确保相同数据生成相同哈希
        ksort($payload);
        
        // 移除 null 值，确保相同的数据生成相同的哈希
        $cleanPayload = array_filter($payload, function ($value) {
            return $value !== null;
        });
        
        // 对键进行排序（因为 array_filter 可能改变键的顺序）
        ksort($cleanPayload);
        
        $hash = md5(json_encode($cleanPayload, JSON_UNESCAPED_UNICODE));
        return (int) hexdec(substr($hash, 0, 15)); // 取前15位避免超出 bigint 范围
    }

    /**
     * 批量记录需要同步的数据变更.
     * 
     * 用于批量操作时提高效率，减少数据库写入次数.
     * 
     * @param array $models 模型数组，格式: [['model' => Model, 'action' => 'updated'], ...]
     * @param string $sourceNode 源节点名称
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

            $syncLogs->each(function ($syncLog) {
                $syncLog->markAsProcessing();
            });

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
    /**
     * 批量接收来自远程节点的同步数据.
     * 
     * 按模型类型分组处理，减少同步监听的开关次数.
     * Media 类型放在最后处理，确保关联的 model 已同步完成.
     * 
     * @param array $batchData 批量数据数组
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

        // Media 类型放在最后处理，确保关联的 model 已经同步完成
        uksort($groupedByModelType, function ($a, $b) {
            $isMediaA = $a === \App\Models\Media::class;
            $isMediaB = $b === \App\Models\Media::class;
            if ($isMediaA && ! $isMediaB) {
                return 1;
            }
            if (! $isMediaA && $isMediaB) {
                return -1;
            }
            return 0;
        });

        // 按模型类型批量处理，每个类型处理时禁用同步监听
        foreach ($groupedByModelType as $modelType => $items) {
            $this->disableSyncForModel($modelType);

            try {
                foreach ($items as $item) {
                    $index = $item['index'];
                    $data = $item['data'];

                    try {
                        $this->validateSyncData($data);

                        // 检查是否应该跳过同步（避免重复或冲突）
                        if ($this->shouldSkipSync($data)) {
                            Log::info('跳过同步', [
                                'model_type' => $data['model_type'] ?? 'unknown',
                                'model_id' => $data['model_id'] ?? null,
                                'action' => $data['action'] ?? null,
                            ]);
                            ++$results['success'];
                            $results['results'][] = [
                                'index' => $index,
                                'sync_log_id' => $data['sync_log_id'] ?? null,
                                'success' => true,
                                'skipped' => true,
                            ];
                            continue;
                        }

                        // 执行同步操作（创建/更新/删除）
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

        // 清除缓存，确保数据一致性
        Cache::flush();

        return $results;
    }

    /**
     * 检查模型是否应该同步.
     * 
     * @param Model $model 模型实例
     * @return bool 是否应该同步
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
     * 
     * @param string $modelType 模型类型
     * @return bool 是否是翻译模型
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
            return true; // 未定义关键字段，默认允许同步
        }

        // 检查所有关键字段是否至少有一个非空
        foreach ($fields as $field) {
            if (! empty($payload[$field] ?? null)) {
                return true;
            }
        }

        return false; // 所有关键字段都为空
    }

    /**
     * 准备同步数据.
     * 
     * 对于删除操作，Pivot 表返回所有字段（用于复合键查找），普通模型只返回 id.
     * 对于创建/更新操作，返回完整的模型数据，并添加 Media 文件信息.
     * 
     * @param Model $model 模型实例
     * @param string $action 操作类型
     * @return array 同步数据
     */
    protected function preparePayload(Model $model, string $action): array
    {
        $isPivot = is_subclass_of(get_class($model), \Illuminate\Database\Eloquent\Relations\Pivot::class);
        
        if ($action === 'deleted') {
            // Pivot 表删除时需要所有字段用于复合键查找
            if ($isPivot) {
                return $model->toArray();
            }
            // 普通模型删除时只需要 id
            return [
                'id' => $model->id,
                'deleted_at' => now()->toIso8601String(),
            ];
        }

        // 创建/更新操作：返回完整数据
        $payload = $model->toArray();
        
        // Pivot 表：只保留 fillable 字段，移除其他字段（如 pivot 相关字段）
        if ($isPivot) {
            $fillableFields = $model->getFillable();
            $cleanPayload = [];
            foreach ($fillableFields as $field) {
                if (isset($payload[$field])) {
                    $cleanPayload[$field] = $payload[$field];
                }
            }
            $payload = $cleanPayload;
        }
        
        $this->normalizeTimestampsInPayload($payload, $model);
        $this->addMediaFileInfo($payload, $model); // Media 模型添加文件下载 URL

        return $payload;
    }

    /**
     * 生成同步哈希值.
     * 
     * 用于判断数据是否发生变化，避免重复同步.
     * 
     * @param Model $model 模型实例
     * @param string $action 操作类型
     * @return string 哈希值
     */
    protected function generateSyncHash(Model $model, string $action): string
    {
        $data = $this->preparePayload($model, $action);
        return md5(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 创建模型.
     * 
     * 基于雪花ID全局唯一性，直接通过ID创建，无需检查唯一字段.
     * 强制使用源节点的 ID，确保不会重新生成 ID.
     * 
     * @param string $modelType 模型类型
     * @param int $modelId 模型ID（雪花ID）
     * @param array $payload 同步数据
     * @return Model|null 创建的模型实例，失败返回 null
     */
    protected function createModel(string $modelType, int $modelId, array $payload): ?Model
    {
        // Media 模型需要移除非数据库字段
        if ($modelType === \App\Models\Media::class) {
            $payload = $this->cleanMediaPayload($payload);
        }

        // Pivot 表使用复合键，单独处理
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        if ($isPivot) {
            return $this->createPivotModel($modelType, $payload);
        }

        // 翻译模型：如果关键字段为空，不创建记录
        if ($this->isTranslationModel($modelType) && ! $this->hasRequiredTranslationFields($modelType, $payload)) {
            return null;
        }

        // 强制使用源节点的 ID，确保不会重新生成
        $payload['id'] = $modelId;
        $this->parseTimestampsInPayload($payload);

        try {
            // 使用 withoutEvents 禁用所有模型事件（包括 HasSnowflakeId 的 creating 事件）
            // 确保使用源节点的 id，不会重新生成
            return $modelType::withoutEvents(function () use ($modelType, $modelId, $payload) {
                $model = new $modelType();
                $model->fill($payload);
                $model->id = $modelId; // 强制设置 ID
                $model->save();
                return $model;
            });
        } catch (\Exception $e) {
            Log::warning('创建同步记录失败', [
                'model_type' => $modelType,
                'model_id' => $modelId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * 更新模型.
     * 
     * 基于雪花ID全局唯一性，直接通过ID查找并更新.
     * 如果模型不存在，则创建新记录（可能是更新操作但记录还未同步）.
     * 
     * @param string $modelType 模型类型
     * @param int $modelId 模型ID（雪花ID）
     * @param array $payload 同步数据
     * @return Model|null 更新的模型实例，失败返回 null
     */
    protected function updateModel(string $modelType, int $modelId, array $payload): ?Model
    {
        // Media 模型需要移除非数据库字段
        if ($modelType === \App\Models\Media::class) {
            $payload = $this->cleanMediaPayload($payload);
        }

        // Pivot 表使用复合键，单独处理
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        if ($isPivot) {
            return $this->updatePivotModel($modelType, $payload);
        }

        // 直接通过雪花ID查找（全局唯一）
        $model = $modelType::find($modelId);
        if (! $model) {
            // 模型不存在，创建新记录（可能是更新操作但记录还未同步）
            return $this->createModel($modelType, $modelId, $payload);
        }

        // 翻译模型：如果关键字段为空，删除该记录而不是更新
        if ($this->isTranslationModel($modelType) && ! $this->hasRequiredTranslationFields($modelType, $payload)) {
            $model->withoutEvents(function () use ($model) {
                $model->delete();
            });
            return null;
        }
        
        // 更新记录：移除 id 字段，因为 update 不应该更新主键
        $updatePayload = $payload;
        unset($updatePayload['id']);
        $this->parseTimestampsInPayload($updatePayload);
        
        // Media 模型使用 withoutEvents 禁用所有事件（防止死循环）
        if ($modelType === \App\Models\Media::class) {
            $model->withoutEvents(function () use ($model, $updatePayload) {
                $model->update($updatePayload);
            });
        } else {
            $model->update($updatePayload);
        }
        
        return $model;
    }

    /**
     * 创建 Pivot 表模型（无主键）.
     * 
     * 如果记录已存在，直接返回现有记录（避免重复）.
     * 
     * @param string $modelType 模型类型
     * @param array $payload 同步数据
     * @return Model|null 创建的模型实例，失败返回 null
     */
    protected function createPivotModel(string $modelType, array $payload): ?Model
    {
        try {
            $cleanPayload = $this->cleanPivotPayload($modelType, $payload);
            if (empty($cleanPayload)) {
                return null;
            }

            // 查找是否存在旧记录（使用复合键）
            $existingModel = $this->findPivotModel($modelType, $cleanPayload);
            if ($existingModel) {
                return $existingModel; // 记录已存在，直接返回（避免重复）
            }
            
            return $modelType::create($cleanPayload);
        } catch (\Exception $e) {
            Log::warning('创建 Pivot 表记录失败', [
                'model_type' => $modelType,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return null;
        }
    }

    /**
     * 更新 Pivot 表模型（无主键）.
     * 
     * 先删除旧记录（如果存在），然后创建新记录，确保数据完全同步.
     * Pivot 表没有主键，不能使用模型的 delete() 方法，需要使用查询构建器直接删除.
     * 
     * @param string $modelType 模型类型
     * @param array $payload 同步数据
     * @return Model|null 更新的模型实例，失败返回 null
     */
    protected function updatePivotModel(string $modelType, array $payload): ?Model
    {
        try {
            $cleanPayload = $this->cleanPivotPayload($modelType, $payload);
            if (empty($cleanPayload)) {
                return null;
            }

            // 查找是否存在旧记录（使用复合键）
            $existingModel = $this->findPivotModel($modelType, $cleanPayload);
            if ($existingModel) {
                // Pivot 表没有主键，使用查询构建器直接删除
                $tableName = $existingModel->getTable();
                $query = \Illuminate\Support\Facades\DB::table($tableName);
                
                // 使用所有字段构建查询条件（复合键）
                foreach ($cleanPayload as $field => $value) {
                    // 严格验证字段名：必须是非空字符串，且长度大于 0
                    if (is_string($field) && strlen($field) > 0 && $value !== null) {
                        $query->where($field, $value);
                    }
                }
                
                $query->delete(); // 先删除旧记录
            }
            
            // 创建新记录（确保数据完全同步）
            return $modelType::create($cleanPayload);
        } catch (\Exception $e) {
            Log::warning('更新 Pivot 表记录失败', [
                'model_type' => $modelType,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return null;
        }
    }

    /**
     * 清理 Pivot 表 payload，移除空键和 null 值，只保留 fillable 字段.
     * 
     * @param string $modelType 模型类型
     * @param array $payload 原始数据
     * @return array 清理后的数据
     */
    protected function cleanPivotPayload(string $modelType, array $payload): array
    {
        $modelInstance = new $modelType();
        $fillableFields = $modelInstance->getFillable();
        
        // 只保留 fillable 字段，且值不为 null
        $cleanPayload = [];
        foreach ($fillableFields as $field) {
            if (!empty($field) && is_string($field) && isset($payload[$field]) && $payload[$field] !== null) {
                $cleanPayload[$field] = $payload[$field];
            }
        }
        
        if (empty($cleanPayload)) {
            Log::warning('Pivot 表 payload 中缺少必要的字段', [
                'model_type' => $modelType,
                'payload' => $payload,
                'fillable_fields' => $fillableFields,
            ]);
        }
        
        return $cleanPayload;
    }

    /**
     * 查找 Pivot 表模型（使用复合键）.
     * 
     * @param string $modelType 模型类型
     * @param array $cleanPayload 清理后的数据（包含所有 fillable 字段）
     * @return Model|null 找到的模型实例，未找到返回 null
     */
    protected function findPivotModel(string $modelType, array $cleanPayload): ?Model
    {
        try {
            // 使用所有字段构建查询条件（复合键）
            $query = $modelType::query();
            foreach ($cleanPayload as $field => $value) {
                if (!empty($field) && is_string($field)) {
                    $query->where($field, $value);
                }
            }
            return $query->first();
        } catch (\Exception $e) {
            Log::warning('查找 Pivot 表记录失败', [
                'model_type' => $modelType,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * 清理 Media payload，移除非数据库字段.
     * 
     * 这些字段是 preparePayload 中添加的计算字段，用于文件下载，不应写入数据库.
     * 
     * @param array $payload 原始数据
     * @return array 清理后的数据
     */
    protected function cleanMediaPayload(array $payload): array
    {
        $fieldsToRemove = ['original_url', 'preview_url', 'file_url', 'file_path', 'file_disk'];
        return array_diff_key($payload, array_flip($fieldsToRemove));
    }

    /**
     * 下载并保存 Media 文件，并生成缩略图.
     * 
     * 此方法在同步接收过程中调用，需要禁用 Media 同步以避免死循环.
     * 
     * @param \App\Models\Media $media Media 模型实例
     * @param array $payload 同步数据（包含 original_url）
     */
    protected function downloadAndSaveMediaFile(
        \App\Models\Media $media,
        array $payload
    ): void {
        // 禁用 Media 同步，防止文件下载和转换过程中触发同步导致死循环
        $wasDisabled = \App\Observers\MediaObserver::$syncDisabled;
        \App\Observers\MediaObserver::$syncDisabled = true;
        
        try {
            $downloadUrl = $payload['original_url'] ?? $payload['file_url'] ?? null;
            if (! $downloadUrl) {
                Log::warning('Media 同步数据缺少下载 URL', [
                    'media_id' => $media->id,
                    'payload_keys' => array_keys($payload),
                ]);
                return;
            }

            // 如果文件已存在，跳过下载，只触发转换
            if ($this->mediaFileExists($media)) {
                Log::info('Media 文件已存在，跳过下载', [
                    'media_id' => $media->id,
                    'file_path' => $this->getMediaFilePath($media),
                ]);
                $this->triggerMediaConversions($media);
                return;
            }

            // 下载并保存文件（失败时抛出异常，让上层处理）
            $this->downloadAndSaveFile($media, $downloadUrl);
            // 文件保存成功后，触发 conversions 生成缩略图
            $this->triggerMediaConversions($media);

            Log::info('Media 文件同步成功', [
                'media_id' => $media->id,
                'file_size' => $this->getMediaFileSize($media),
            ]);
        } finally {
            // 恢复之前的同步状态
            \App\Observers\MediaObserver::$syncDisabled = $wasDisabled;
        }
    }

    /**
     * 删除模型.
     * 
     * 基于雪花ID全局唯一性，普通模型直接通过ID删除.
     * Pivot 表使用复合键删除.
     * 
     * @param string $modelType 模型类型
     * @param int $modelId 模型ID（雪花ID）
     * @param array $payload 同步数据（Pivot 表需要）
     */
    protected function deleteModel(string $modelType, int $modelId, array $payload = []): void
    {
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        if ($isPivot) {
            // Pivot 表使用复合键删除
            $this->deletePivotModel($modelType, $payload);
            return;
        }

        // 普通模型：直接通过雪花ID删除（全局唯一）
        $model = $modelType::find($modelId);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * 删除 Pivot 表模型（使用复合键）.
     * 
     * Pivot 表没有主键，不能使用模型的 delete() 方法，需要使用查询构建器直接删除.
     * 
     * @param string $modelType 模型类型
     * @param array $payload 同步数据
     */
    protected function deletePivotModel(string $modelType, array $payload): void
    {
        try {
            $cleanPayload = $this->cleanPivotPayload($modelType, $payload);
            if (empty($cleanPayload)) {
                return;
            }

            // 获取表名
            $modelInstance = new $modelType();
            $tableName = $modelInstance->getTable();
            
            // 使用查询构建器直接删除（Pivot 表没有主键，不能使用模型的 delete() 方法）
            $query = \Illuminate\Support\Facades\DB::table($tableName);
            
            // 使用所有字段构建查询条件（复合键）
            foreach ($cleanPayload as $field => $value) {
                // 严格验证字段名：必须是非空字符串，且长度大于 0
                if (is_string($field) && strlen($field) > 0 && $value !== null) {
                    $query->where($field, $value);
                }
            }
            
            // 执行删除
            $deletedCount = $query->delete();
            
            if ($deletedCount > 1) {
                Log::warning('删除 Pivot 表记录：找到多条匹配记录，已全部删除', [
                    'model_type' => $modelType,
                    'count' => $deletedCount,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('删除 Pivot 表记录失败', [
                'model_type' => $modelType,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }

    /**
     * 查找所有匹配的 Pivot 表模型（使用复合键）.
     * 
     * @param string $modelType 模型类型
     * @param array $cleanPayload 清理后的数据
     * @return \Illuminate\Database\Eloquent\Collection 匹配的模型集合
     */
    protected function findPivotModels(string $modelType, array $cleanPayload): \Illuminate\Database\Eloquent\Collection
    {
        try {
            // 使用所有字段构建查询条件（复合键）
            $query = $modelType::query();
            foreach ($cleanPayload as $field => $value) {
                if (!empty($field) && is_string($field)) {
                    $query->where($field, $value);
                }
            }
            return $query->get();
        } catch (\Exception $e) {
            Log::warning('查找 Pivot 表记录失败', [
                'model_type' => $modelType,
                'error' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    /**
     * 临时禁用模型的同步监听.
     * 
     * 在批量同步时使用，避免同步过程中触发新的同步导致死循环.
     * 
     * @param string $modelType 模型类型
     */
    protected function disableSyncForModel(string $modelType): void
    {
        if (! class_exists($modelType)) {
            return;
        }

        // Media 模型使用 Observer 的静态属性
        if ($modelType === \App\Models\Media::class
            || is_subclass_of($modelType, \App\Models\Media::class)) {
            \App\Observers\MediaObserver::$syncDisabled = true;
        } else {
            // 其他模型使用 Trait 的静态属性
            $modelType::$syncDisabled = true;
        }
    }

    /**
     * 重新启用模型的同步监听.
     * 
     * @param string $modelType 模型类型
     */
    protected function enableSyncForModel(string $modelType): void
    {
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
     * 
     * @return array 目标节点名称数组
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
     * 
     * 删除操作总是创建日志，其他操作需要检查是否真的需要同步（避免重复）.
     * 
     * @param string $modelType 模型类型
     * @param int $modelId 模型ID
     * @param string $action 操作类型
     * @param string $targetNode 目标节点
     * @param string $syncHash 同步哈希值
     * @return bool 是否应该创建同步日志
     */
    protected function shouldCreateSyncLog(
        string $modelType,
        int $modelId,
        string $action,
        string $targetNode,
        string $syncHash
    ): bool {
        // 删除操作总是创建日志
        if ($action === 'deleted') {
            return true;
        }

        // Pivot 表：由于使用哈希值作为 model_id，相同数据会生成相同的 model_id
        // 所以可以使用 SyncStatus 来检查是否需要同步
        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        if ($isPivot) {
            // 对于 Pivot 表，使用 SyncStatus 检查（基于 model_id 和 sync_hash）
            return SyncStatus::needsSync($modelType, $modelId, $targetNode, $syncHash);
        }

        // 其他操作检查是否真的需要同步（避免重复）
        return SyncStatus::needsSync($modelType, $modelId, $targetNode, $syncHash);
    }

    /**
     * 创建同步日志.
     * 
     * @param string $modelType 模型类型
     * @param int $modelId 模型ID
     * @param string $action 操作类型
     * @param string $sourceNode 源节点
     * @param string $targetNode 目标节点
     * @param array $payload 同步数据
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
     * 
     * 按模型类型和操作类型分组，提高批量处理效率.
     * 
     * @param array $models 模型数组
     * @return array 分组后的模型数组
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

            // 按模型类型和操作类型分组
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
     * 
     * 为每个模型和每个目标节点创建同步日志.
     * 
     * @param array $groupedModels 分组后的模型数组
     * @param array $targetNodes 目标节点数组
     * @param string $sourceNode 源节点
     * @return array 同步日志数组
     */
    protected function buildBatchSyncLogs(
        array $groupedModels,
        array $targetNodes,
        string $sourceNode
    ): array {
        $syncLogs = [];

        // 为每个目标节点和每个模型创建同步日志
        foreach ($targetNodes as $targetNode) {
            foreach ($groupedModels as $group) {
                foreach ($group['models'] as $model) {
                    $syncHash = $this->generateSyncHash($model, $group['action']);

                    // 检查是否应该创建同步日志（避免重复）
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
     * 
     * 将 payload 序列化为 JSON，然后分批插入数据库.
     * 
     * @param array $syncLogs 同步日志数组
     */
    protected function insertBatchSyncLogs(array $syncLogs): void
    {
        if (empty($syncLogs)) {
            return;
        }

        // 确保 payload 正确序列化为 JSON
        foreach ($syncLogs as &$log) {
            if (isset($log['payload']) && is_array($log['payload'])) {
                $log['payload'] = json_encode($log['payload'], JSON_UNESCAPED_UNICODE);
            }
        }
        unset($log);

        // 分批插入，避免单次插入数据过多
        $batchSize = config('sync.batch_size', 100);
        foreach (array_chunk($syncLogs, $batchSize) as $chunk) {
            SyncLog::insert($chunk);
        }
    }

    /**
     * 获取远程节点配置.
     * 
     * @param string $targetNode 目标节点名称
     * @return array 远程节点配置
     * @throws \Exception 配置不存在或未设置 API Key
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
     * 发送批量同步请求.
     * 
     * @param array $batchData 批量数据
     * @param array $remoteConfig 远程节点配置
     * @return \Illuminate\Http\Client\Response HTTP 响应
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
     * 
     * 根据返回结果更新同步日志状态，统计成功和失败数量.
     * 
     * @param \Illuminate\Support\Collection $syncLogs 同步日志集合
     * @param array $result 远程节点返回的结果
     * @param string $targetNode 目标节点
     * @return array ['success' => int, 'failed' => int, 'errors' => array]
     */
    protected function handleBatchSyncResult(
        \Illuminate\Support\Collection $syncLogs,
        array $result,
        string $targetNode
    ): array {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        $syncLogMap = $syncLogs->keyBy('id'); // 创建 ID 到 syncLog 的映射

        if (isset($result['results']) && is_array($result['results'])) {
            // 处理详细结果
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
     * 标记同步日志为完成，并更新同步状态（用于避免重复同步）.
     * 删除操作和 Pivot 表不需要更新同步状态.
     * 
     * @param SyncLog $syncLog 同步日志
     */
    protected function handleSuccessfulSync(SyncLog $syncLog): void
    {
        $syncLog->markAsCompleted();

        // 删除操作不需要更新同步状态（记录已不存在）
        if ($syncLog->action === 'deleted') {
            return;
        }

        // Pivot 表不需要更新同步状态（没有主键，无法通过 ID 查找）
        $isPivot = is_subclass_of($syncLog->model_type, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        if ($isPivot) {
            return;
        }

        // 更新同步状态，用于避免重复同步
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
     * 
     * 记录错误日志并标记同步日志为失败.
     * 
     * @param SyncLog $syncLog 同步日志
     * @param \Exception $e 异常
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
     * 
     * 检查模型类型是否在同步列表中.
     * 
     * @param array $data 同步数据
     * @throws \Exception 模型不在同步列表中
     */
    protected function validateSyncData(array $data): void
    {
        $modelType = $data['model_type'] ?? null;

        if (! $modelType || ! in_array($modelType, config('sync.sync_models'))) {
            throw new \Exception("模型不在同步列表中: {$modelType}");
        }
    }

    /**
     * 判断是否应该跳过同步.
     *
     * 基于雪花ID全局唯一性：
     * - 创建操作：如果ID已存在，跳过（避免重复）
     * - 更新操作：如果本地数据更新，跳过（以最新为准）
     * - 删除操作：不跳过，必须执行
     * 
     * @param array $data 同步数据
     * @return bool 是否应该跳过
     */
    protected function shouldSkipSync(array $data): bool
    {
        $action = $data['action'] ?? null;
        $modelType = $data['model_type'];
        $modelId = $data['model_id'];
        $timestamp = $data['timestamp'] ?? now()->toIso8601String();
        $payload = $data['payload'] ?? [];

        // 删除操作不应该跳过，必须执行
        if ($action === 'deleted') {
            return false;
        }

        $isPivot = is_subclass_of($modelType, \Illuminate\Database\Eloquent\Relations\Pivot::class);
        if ($isPivot) {
            // Pivot 表：创建操作时，如果记录已存在，跳过（避免重复）
            if ($action === 'created') {
                try {
                    $cleanPayload = $this->cleanPivotPayload($modelType, $payload);
                    if (empty($cleanPayload)) {
                        return false;
                    }
                    return $this->findPivotModel($modelType, $cleanPayload) !== null;
                } catch (\Exception $e) {
                    return false;
                }
            }
            // 更新操作不跳过
            return false;
        }

        // 普通模型：直接通过雪花ID查找（全局唯一）
        $existingModel = $modelType::find($modelId);
        if ($action === 'created') {
            // 创建操作：如果ID已存在，跳过（避免重复）
            return $existingModel !== null;
        }
        
        if ($action === 'updated' && $existingModel && $existingModel->updated_at) {
            // 更新操作：如果本地数据更新，跳过（以最新为准）
            return $existingModel->updated_at->gt(Carbon::parse($timestamp));
        }

        return false;
    }

    /**
     * 处理同步操作.
     * 
     * 将创建、更新、删除分开处理，逻辑更清晰.
     * Media 模型在创建/更新后需要下载文件并生成缩略图.
     * 
     * @param array $data 同步数据
     */
    protected function processSyncAction(array $data): void
    {
        $modelType = $data['model_type'];
        $modelId = $data['model_id'];
        $action = $data['action'];
        $payload = $data['payload'];

        switch ($action) {
            case 'created':
                $model = $this->createModel($modelType, $modelId, $payload);
                // Media 模型需要下载文件并生成缩略图
                if ($model && $model instanceof \App\Models\Media) {
                    $this->downloadAndSaveMediaFile($model, $payload);
                }
                break;
                
            case 'updated':
                $model = $this->updateModel($modelType, $modelId, $payload);
                // Media 模型需要下载文件并生成缩略图
                if ($model && $model instanceof \App\Models\Media) {
                    $this->downloadAndSaveMediaFile($model, $payload);
                }
                break;
                
            case 'deleted':
                $this->deleteModel($modelType, $modelId, $payload);
                break;
        }
    }

    /**
     * 规范化 payload 中的时间戳.
     * 
     * 将时间戳统一转换为 ISO8601 格式字符串.
     * 
     * @param array &$payload 同步数据（引用传递）
     * @param Model $model 模型实例
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
     * 添加媒体文件信息到 payload.
     * 
     * Media 模型添加 original_url，用于同步时下载文件.
     * 
     * @param array &$payload 同步数据（引用传递）
     * @param Model $model 模型实例
     */
    protected function addMediaFileInfo(array &$payload, Model $model): void
    {
        if ($model instanceof \App\Models\Media) {
            $payload['original_url'] = $model->getUrl();
        }
    }

    /**
     * 解析 payload 中的时间戳为 Carbon 实例.
     * 
     * 用于在创建/更新模型时正确设置时间戳.
     * 
     * @param array &$payload 同步数据（引用传递）
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
     * 下载并保存文件（带重试机制）.
     * 
     * 使用指数退避策略重试，最多重试 3 次.
     * 对于图片文件，会验证文件有效性.
     * 
     * @param \App\Models\Media $media Media 模型实例
     * @param string $downloadUrl 下载 URL
     * @throws \Exception 下载失败时抛出异常
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
                
                // 验证图片文件有效性
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
                
                return;
                
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning('Media 文件下载失败，准备重试', [
                    'media_id' => $media->id,
                    'url' => $downloadUrl,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                ]);
                
                // 指数退避：每次重试延迟时间翻倍
                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                    $retryDelay *= 2;
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
     * 
     * @param \App\Models\Media $media Media 模型实例
     * @param string $fileContent 文件内容
     * @throws \Exception 文件保存失败
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
     * 
     * 使用 Spatie Media Library 的 PathGenerator 生成正确的路径.
     * 
     * @param \App\Models\Media $media Media 模型实例
     * @return string 文件相对路径
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
     * 
     * @param \App\Models\Media $media Media 模型实例
     * @return bool 文件是否存在
     */
    protected function mediaFileExists(\App\Models\Media $media): bool
    {
        $disk = $media->disk ?? config('media-library.disk_name', 'public');
        return \Illuminate\Support\Facades\Storage::disk($disk)->exists($this->getMediaFilePath($media));
    }

    /**
     * 获取 Media 文件大小.
     * 
     * @param \App\Models\Media $media Media 模型实例
     * @return int|null 文件大小（字节），文件不存在返回 null
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
     * 此方法在同步接收过程中调用，Media 同步应该已经被禁用.
     * 使用自定义的 Job 包装器，在执行时禁用 Media 同步，防止死循环.
     * 
     * @param \App\Models\Media $media Media 模型实例
     */
    protected function triggerMediaConversions(\App\Models\Media $media): void
    {
        // 确保 Media 同步被禁用（防止转换过程中触发同步导致死循环）
        $wasDisabled = \App\Observers\MediaObserver::$syncDisabled;
        \App\Observers\MediaObserver::$syncDisabled = true;
        
        try {
            $media->refresh();
            $model = $this->getMediaModel($media);
            if (! $model) {
                return;
            }

            // 检查 model 是否实现了 HasMedia 接口
            if (! method_exists($model, 'registerMediaConversions')) {
                return;
            }

            // 获取 conversions collection
            $conversions = \Spatie\MediaLibrary\Conversions\ConversionCollection::createForMedia($media);
            if ($conversions->isEmpty()) {
                return;
            }
            
            // 使用自定义的 Job 包装器，在执行时禁用 Media 同步
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
     * 
     * 用于 Media 转换时获取关联的模型实例.
     * 
     * @param \App\Models\Media $media Media 模型实例
     * @return Model|null 关联的模型实例，未找到返回 null
     */
    protected function getMediaModel(\App\Models\Media $media): ?Model
    {
        $modelType = $media->model_type;
        $modelId = $media->model_id;
        
        if (! $modelType || ! $modelId) {
            return null;
        }
        
        if (! class_exists($modelType)) {
            return null;
        }
        
        return $modelType::find($modelId);
    }
}
