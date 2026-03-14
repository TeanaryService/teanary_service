<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Enums\TranslationStatusEnum;
use App\Observers\ProductObserver;
use App\Traits\CascadesMediaDeletes;
use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Product.
 *
 * @property int $id
 * @property string $slug
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|CartItem[] $cartItems
 * @property Collection|OrderItem[] $orderItems
 * @property Collection|AttributeValue[] $attributeValues
 * @property Collection|ProductCategory[] $productCategories
 * @property Collection|ProductTranslation[] $productTranslations
 * @property Collection|ProductVariant[] $productVariants
 */
#[ObservedBy([ProductObserver::class])]

class Product extends Model implements HasMedia
{
    use CascadesMediaDeletes;
    use HasFactory;
    use HasSnowflakeId;
    use InteractsWithMedia;
    use Searchable;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'status' => ProductStatusEnum::class,
        'translation_status' => TranslationStatusEnum::class,
    ];

    protected $fillable = [
        'slug',
        'status',
        'source_url',
        'translation_status',
    ];

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value')
            ->withPivot('attribute_id')
            ->using(\App\Models\ProductAttributeValue::class);
    }

    /**
     * 同步属性值并触发同步.
     */
    public function syncAttributeValues(array $ids, bool $detaching = true): array
    {
        // 在同步之前，先获取要删除的记录（用于同步）
        $pivotsToDelete = [];
        if (config('sync.enabled') && $detaching) {
            // 获取当前已关联的属性值ID
            $currentAttributeValueIds = $this->attributeValues()->pluck('attribute_value_id')->toArray();
            // 计算要删除的属性值ID
            $idsToKeep = array_keys($ids);
            $idsToDelete = array_diff($currentAttributeValueIds, $idsToKeep);

            // 获取要删除的完整记录
            if (! empty($idsToDelete)) {
                $pivotsToDelete = \App\Models\ProductAttributeValue::where('product_id', $this->id)
                    ->whereIn('attribute_value_id', $idsToDelete)
                    ->get()
                    ->keyBy('attribute_value_id');
            }
        }

        // 禁用 ProductAttributeValue 的自动同步（因为我们会在下面手动触发同步）
        $wasSyncDisabled = \App\Models\ProductAttributeValue::$syncDisabled;
        \App\Models\ProductAttributeValue::$syncDisabled = true;

        try {
            $changes = $this->attributeValues()->sync($ids, $detaching);
        } finally {
            // 恢复同步状态
            \App\Models\ProductAttributeValue::$syncDisabled = $wasSyncDisabled;
        }

        // 手动触发 Pivot 模型的同步
        // 使用 afterCommit 确保在事务提交后再执行同步
        if (config('sync.enabled')) {
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($changes, $ids, $pivotsToDelete) {
                $this->processAttributeValueSync($changes, $ids, $pivotsToDelete);
            });
        }

        return $changes;
    }

    /**
     * 处理属性值同步（在事务提交后执行）.
     */
    protected function processAttributeValueSync(array $changes, array $ids, $pivotsToDelete): void
    {
        $syncService = app(\App\Services\SyncService::class);
        $currentNode = config('sync.node');

        // 处理新增的记录
        foreach ($changes['attached'] ?? [] as $index => $pivotData) {
            $this->processAttachedAttributeValue($syncService, $currentNode, $index, $pivotData, $ids);
        }

        // 处理更新的记录
        foreach ($changes['updated'] ?? [] as $index => $pivotData) {
            $this->processUpdatedAttributeValue($syncService, $currentNode, $index, $pivotData, $ids);
        }

        // 处理删除的记录
        foreach ($changes['detached'] ?? [] as $attributeValueId) {
            $this->processDetachedAttributeValue($syncService, $currentNode, $attributeValueId, $pivotsToDelete);
        }
    }

    /**
     * 处理新增的属性值关联.
     */
    protected function processAttachedAttributeValue($syncService, string $currentNode, $index, $pivotData, array $ids): void
    {
        [$attributeValueId, $attributeId] = $this->resolveAttributeIds($index, $pivotData, $ids, '创建');

        if (! $attributeValueId) {
            return;
        }

        $pivot = $this->findOrCreatePivot($attributeValueId, $attributeId);

        if ($pivot) {
            $syncService->recordSync($pivot, 'created', $currentNode);
        }
    }

    /**
     * 处理更新的属性值关联.
     */
    protected function processUpdatedAttributeValue($syncService, string $currentNode, $index, $pivotData, array $ids): void
    {
        [$attributeValueId, $attributeId] = $this->resolveAttributeIds($index, $pivotData, $ids, '更新');

        if (! $attributeValueId) {
            return;
        }

        $pivot = $this->findOrCreatePivot($attributeValueId, $attributeId);

        if ($pivot) {
            $syncService->recordSync($pivot, 'updated', $currentNode);
        }
    }

    /**
     * 处理删除的属性值关联.
     */
    protected function processDetachedAttributeValue($syncService, string $currentNode, int $attributeValueId, $pivotsToDelete): void
    {
        $pivot = $pivotsToDelete->get($attributeValueId);

        if ($pivot) {
            $syncService->recordSync($pivot, 'deleted', $currentNode);

            return;
        }

        // 尝试从数据库查询
        $pivot = \App\Models\ProductAttributeValue::where('product_id', $this->id)
            ->where('attribute_value_id', $attributeValueId)
            ->first();

        if ($pivot) {
            $syncService->recordSync($pivot, 'deleted', $currentNode);

            return;
        }

        // 尝试通过 attribute_value 查找 attribute_id
        $attributeValue = \App\Models\AttributeValue::find($attributeValueId);
        if ($attributeValue?->attribute_id) {
            $pivot = new \App\Models\ProductAttributeValue([
                'product_id' => $this->id,
                'attribute_value_id' => $attributeValueId,
                'attribute_id' => $attributeValue->attribute_id,
            ]);
            $syncService->recordSync($pivot, 'deleted', $currentNode);

            return;
        }

        // 最后的后备方案
        \Illuminate\Support\Facades\Log::warning('ProductAttributeValue 删除同步：无法获取完整记录', [
            'product_id' => $this->id,
            'attribute_value_id' => $attributeValueId,
        ]);

        $pivot = new \App\Models\ProductAttributeValue([
            'product_id' => $this->id,
            'attribute_value_id' => $attributeValueId,
        ]);
        $syncService->recordSync($pivot, 'deleted', $currentNode);
    }

    /**
     * 解析属性值ID和属性ID.
     */
    protected function resolveAttributeIds($index, $pivotData, array $ids, string $action): array
    {
        $attributeValueId = null;
        $attributeId = $this->extractAttributeIdFromPivotData($pivotData);

        // 从 $ids 数组中找到对应的 attribute_value_id
        if ($attributeId !== null) {
            $attributeValueId = $this->findAttributeValueIdByAttributeId($ids, $attributeId);
        }

        // 如果还是无法确定，尝试使用索引从 $ids 中获取
        if ($attributeValueId === null && is_numeric($index)) {
            [$attributeValueId, $attributeId] = $this->findAttributeIdsByIndex($ids, $index, $attributeId);
        }

        // 验证数据完整性
        if (empty($attributeValueId) || $attributeValueId === 0) {
            \Illuminate\Support\Facades\Log::warning("ProductAttributeValue {$action}同步：无法确定 attribute_value_id", [
                'product_id' => $this->id,
                'index' => $index,
                'pivot_data' => $pivotData,
                'ids_keys' => array_keys($ids),
                'ids' => $ids,
            ]);

            return [null, null];
        }

        // 如果还没有 attributeId，尝试从 $ids 或数据库获取
        if ($attributeId === null) {
            $attributeId = $this->resolveAttributeId($attributeValueId, $ids);
        }

        return [$attributeValueId, $attributeId];
    }

    /**
     * 从 pivot 数据中提取 attribute_id.
     */
    protected function extractAttributeIdFromPivotData($pivotData): ?int
    {
        if (is_array($pivotData)) {
            return $pivotData['attribute_id'] ?? null;
        }

        if (is_numeric($pivotData) && $pivotData > 0) {
            return (int) $pivotData;
        }

        return null;
    }

    /**
     * 通过 attribute_id 查找 attribute_value_id.
     */
    protected function findAttributeValueIdByAttributeId(array $ids, int $attributeId): ?int
    {
        foreach ($ids as $key => $value) {
            $valueAttributeId = is_array($value) ? ($value['attribute_id'] ?? null) : null;

            if ($valueAttributeId === $attributeId) {
                return is_numeric($key) ? (int) $key : $key;
            }
        }

        return null;
    }

    /**
     * 通过索引查找 attribute_value_id 和 attribute_id.
     */
    protected function findAttributeIdsByIndex(array $ids, int $index, ?int $existingAttributeId): array
    {
        $idsKeys = array_keys($ids);
        $idsValues = array_values($ids);

        if (! isset($idsKeys[$index])) {
            return [null, $existingAttributeId];
        }

        $attributeValueId = $idsKeys[$index];
        $attributeId = $existingAttributeId;

        if (isset($idsValues[$index]) && is_array($idsValues[$index])) {
            $attributeId = $idsValues[$index]['attribute_id'] ?? $attributeId;
        }

        return [$attributeValueId, $attributeId];
    }

    /**
     * 解析 attribute_id（从 $ids 或数据库）.
     */
    protected function resolveAttributeId(int $attributeValueId, array $ids): ?int
    {
        // 从 $ids 中获取
        if (isset($ids[$attributeValueId]) && is_array($ids[$attributeValueId])) {
            return $ids[$attributeValueId]['attribute_id'] ?? null;
        }

        // 从数据库查询
        $attributeValue = \App\Models\AttributeValue::find($attributeValueId);

        return $attributeValue?->attribute_id;
    }

    /**
     * 查找或创建 pivot 记录.
     */
    protected function findOrCreatePivot(?int $attributeValueId, ?int $attributeId)
    {
        if (! $attributeValueId) {
            return null;
        }

        // 先尝试精确查询
        if ($attributeId !== null) {
            $pivot = \App\Models\ProductAttributeValue::where([
                'product_id' => $this->id,
                'attribute_value_id' => $attributeValueId,
                'attribute_id' => $attributeId,
            ])->first();

            if ($pivot) {
                return $pivot;
            }
        }

        // 尝试不限制 attribute_id 查询
        $pivot = \App\Models\ProductAttributeValue::where([
            'product_id' => $this->id,
            'attribute_value_id' => $attributeValueId,
        ])->first();

        if ($pivot) {
            return $pivot;
        }

        // 如果查询不到且数据完整，创建临时对象用于同步
        if ($attributeId !== null) {
            return new \App\Models\ProductAttributeValue([
                'product_id' => $this->id,
                'attribute_value_id' => $attributeValueId,
                'attribute_id' => $attributeId,
            ]);
        }

        // 最后的后备方案：记录警告日志
        \Illuminate\Support\Facades\Log::warning('ProductAttributeValue 同步：无法获取完整数据', [
            'product_id' => $this->id,
            'attribute_value_id' => $attributeValueId,
            'attribute_id' => $attributeId,
        ]);

        return null;
    }

    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category')
            ->using(\App\Models\ProductCategory::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse');
    }

    /**
     * 同步分类并触发同步.
     */
    public function syncProductCategories(array $ids, bool $detaching = true): array
    {
        $changes = $this->productCategories()->sync($ids, $detaching);

        // 手动触发 Pivot 模型的同步
        if (config('sync.enabled')) {
            $syncService = app(\App\Services\SyncService::class);
            $currentNode = config('sync.node');

            // 处理新增的记录
            foreach ($changes['attached'] ?? [] as $categoryId) {
                $pivot = \App\Models\ProductCategory::where([
                    'product_id' => $this->id,
                    'category_id' => $categoryId,
                ])->first();

                if ($pivot) {
                    $syncService->recordSync($pivot, 'created', $currentNode);
                }
            }

            // 处理删除的记录
            foreach ($changes['detached'] ?? [] as $categoryId) {
                $pivot = new \App\Models\ProductCategory([
                    'product_id' => $this->id,
                    'category_id' => $categoryId,
                ]);
                $syncService->recordSync($pivot, 'deleted', $currentNode);
            }
        }

        return $changes;
    }

    public function productTranslations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * 只查询启用状态的商品
     */
    public function scopeActive($query)
    {
        return $query->where('status', ProductStatusEnum::Active);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(350)
            ->height(350)
            ->sharpen(10)
            ->nonOptimized();
    }

    /**
     * 获取模型的索引化数据数组。
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        // 合并所有翻译的 title 和 content
        $translations = $this->productTranslations;
        $mergedText = '';
        foreach ($translations as $translation) {
            $mergedText .= strip_tags($translation->name ?? '').' ';
            $mergedText .= strip_tags($translation->description ?? '').' ';
        }
        $array['content'] = trim($mergedText);

        return $array;
    }
}
