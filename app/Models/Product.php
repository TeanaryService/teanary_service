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
            if (!empty($idsToDelete)) {
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
        if (config('sync.enabled')) {
            $syncService = app(\App\Services\SyncService::class);
            $currentNode = config('sync.node');
            
            // 刷新数据库连接，确保 sync 操作已提交
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            
            // 处理新增的记录
            foreach ($changes['attached'] ?? [] as $attributeValueId => $pivotData) {
                // 如果 attributeValueId 为 0，可能是 Laravel sync 返回的键是数字索引
                // 尝试从 pivotData 中获取真正的 attribute_value_id
                $realAttributeValueId = $attributeValueId;
                if (empty($attributeValueId) || $attributeValueId === 0) {
                    if (is_array($pivotData)) {
                        // 如果 pivotData 是数组，可能包含 attribute_value_id
                        $realAttributeValueId = $pivotData['attribute_value_id'] ?? $pivotData['id'] ?? null;
                    } elseif (is_numeric($pivotData) && $pivotData > 0) {
                        // 如果 pivotData 是数字且大于 0，可能是 attribute_value_id
                        $realAttributeValueId = $pivotData;
                    }
                    
                    // 如果还是无法获取，尝试从 $ids 的键中查找
                    if (empty($realAttributeValueId) || $realAttributeValueId === 0) {
                        // 遍历 $ids 查找可能的 attribute_value_id
                        foreach ($ids as $key => $value) {
                            if (is_numeric($key) && $key > 0) {
                                $realAttributeValueId = $key;
                                $pivotData = $value; // 更新 pivotData
                                break;
                            }
                        }
                    }
                }
                
                // 如果仍然无法获取有效的 attribute_value_id，记录警告但继续处理
                if (empty($realAttributeValueId) || $realAttributeValueId === 0) {
                    \Illuminate\Support\Facades\Log::warning('ProductAttributeValue 创建同步：无法确定 attribute_value_id，尝试从数据库查询', [
                        'product_id' => $this->id,
                        'original_attribute_value_id' => $attributeValueId,
                        'pivot_data' => $pivotData,
                        'ids_keys' => array_keys($ids),
                        'ids' => $ids,
                    ]);
                }
                
                // 优先从原始 $ids 中获取 attribute_id（最可靠的数据源）
                // 注意：需要处理键的类型转换（字符串 vs 数字）
                $attributeId = null;
                
                // 尝试多种键格式（使用真实的 attribute_value_id）
                $possibleKeys = [
                    $realAttributeValueId,
                    (string) $realAttributeValueId,
                    (int) $realAttributeValueId,
                    $attributeValueId, // 也尝试原始的键
                    (string) $attributeValueId,
                    (int) $attributeValueId,
                ];
                
                foreach ($possibleKeys as $key) {
                    if (isset($ids[$key])) {
                        if (is_array($ids[$key])) {
                            $attributeId = $ids[$key]['attribute_id'] ?? null;
                            break;
                        } elseif (is_numeric($ids[$key])) {
                            // 如果 $ids 的值是数字，可能是 attribute_id
                            $attributeId = $ids[$key];
                            break;
                        }
                    }
                }
                
                // 如果从 $ids 中无法获取，尝试从 sync 返回的 pivotData 中获取
                if ($attributeId === null) {
                    if (is_array($pivotData)) {
                        $attributeId = $pivotData['attribute_id'] ?? null;
                    } elseif (is_numeric($pivotData) && $pivotData > 0) {
                        // 如果 pivotData 是数字且大于 0，可能是 attribute_id（但需要验证）
                        // 这里不直接使用，因为可能是 attribute_value_id
                    }
                }
                
                // 查询 pivot 记录（优先使用 attribute_id，如果查询不到则尝试不限制 attribute_id）
                // 使用真实的 attribute_value_id
                $pivot = null;
                if ($attributeId !== null && $realAttributeValueId > 0) {
                    $pivot = \App\Models\ProductAttributeValue::where([
                        'product_id' => $this->id,
                        'attribute_value_id' => $realAttributeValueId,
                        'attribute_id' => $attributeId,
                    ])->first();
                }
                
                // 如果查询不到，尝试不限制 attribute_id 查询
                if (! $pivot && $realAttributeValueId > 0) {
                    $pivot = \App\Models\ProductAttributeValue::where([
                        'product_id' => $this->id,
                        'attribute_value_id' => $realAttributeValueId,
                    ])->first();
                }
                
                // 如果还是查询不到，尝试从 attribute_value 获取 attribute_id 后重新查询
                $attributeValue = null;
                if (! $pivot && $realAttributeValueId > 0) {
                    $attributeValue = \App\Models\AttributeValue::find($realAttributeValueId);
                    if ($attributeValue && $attributeValue->attribute_id) {
                        // 如果之前没有 attributeId，现在从 attributeValue 获取
                        if ($attributeId === null) {
                            $attributeId = $attributeValue->attribute_id;
                        }
                        
                        $pivot = \App\Models\ProductAttributeValue::where([
                            'product_id' => $this->id,
                            'attribute_value_id' => $realAttributeValueId,
                            'attribute_id' => $attributeValue->attribute_id,
                        ])->first();
                    }
                }
                
                if ($pivot) {
                    $syncService->recordSync($pivot, 'created', $currentNode);
                } else {
                    // 如果还是查询不到，使用已知数据创建临时 pivot 对象用于同步
                    $finalAttributeId = $attributeId;
                    if ($finalAttributeId === null && $attributeValue) {
                        $finalAttributeId = $attributeValue->attribute_id ?? null;
                    }
                    
                    // 如果还是没有，最后尝试从数据库查询 attribute_value
                    if ($finalAttributeId === null && $realAttributeValueId > 0) {
                        $attributeValue = $attributeValue ?? \App\Models\AttributeValue::find($realAttributeValueId);
                        if ($attributeValue) {
                            $finalAttributeId = $attributeValue->attribute_id ?? null;
                        }
                    }
                    
                    // 如果仍然无法获取 attribute_value_id，尝试从所有已关联的 attribute_values 中查找
                    if (($realAttributeValueId === 0 || empty($realAttributeValueId)) && $finalAttributeId !== null) {
                        // 通过 attribute_id 查找可能的 attribute_value_id
                        $possibleAttributeValue = \App\Models\AttributeValue::where('attribute_id', $finalAttributeId)
                            ->whereHas('products', function ($query) {
                                $query->where('product_id', $this->id);
                            })
                            ->first();
                        
                        if ($possibleAttributeValue) {
                            $realAttributeValueId = $possibleAttributeValue->id;
                        }
                    }
                    
                    // 验证数据完整性：attribute_value_id 和 attribute_id 都必须有效
                    if ($finalAttributeId !== null && $realAttributeValueId > 0) {
                        $pivot = new \App\Models\ProductAttributeValue([
                            'product_id' => $this->id,
                            'attribute_value_id' => $realAttributeValueId,
                            'attribute_id' => $finalAttributeId,
                        ]);
                        $syncService->recordSync($pivot, 'created', $currentNode);
                    } else {
                        // 最后的后备方案：记录警告日志，但不跳过（尝试使用已知数据）
                        \Illuminate\Support\Facades\Log::warning('ProductAttributeValue 创建同步：无法获取完整数据，尝试使用部分数据', [
                            'product_id' => $this->id,
                            'original_attribute_value_id' => $attributeValueId,
                            'real_attribute_value_id' => $realAttributeValueId,
                            'attribute_id' => $finalAttributeId,
                            'pivot_data' => $pivotData,
                            'pivot_data_type' => gettype($pivotData),
                            'ids_data' => $ids[$realAttributeValueId] ?? $ids[$attributeValueId] ?? null,
                            'ids_keys' => array_keys($ids),
                            'attribute_value_exists' => $attributeValue !== null,
                        ]);
                        
                        // 即使数据不完整，也尝试同步（可能目标节点能处理）
                        if ($finalAttributeId !== null) {
                            // 使用 product_id 和 attribute_id 创建记录（attribute_value_id 为 0）
                            // 这虽然不理想，但至少能同步部分数据
                            $pivot = new \App\Models\ProductAttributeValue([
                                'product_id' => $this->id,
                                'attribute_value_id' => $realAttributeValueId ?: 0, // 如果无法获取，使用 0
                                'attribute_id' => $finalAttributeId,
                            ]);
                            $syncService->recordSync($pivot, 'created', $currentNode);
                        }
                    }
                }
            }
            
            // 处理更新的记录
            foreach ($changes['updated'] ?? [] as $attributeValueId => $pivotData) {
                // 如果 attributeValueId 为 0，可能是 Laravel sync 返回的键是数字索引
                // 尝试从 pivotData 中获取真正的 attribute_value_id
                $realAttributeValueId = $attributeValueId;
                if (empty($attributeValueId) || $attributeValueId === 0) {
                    if (is_array($pivotData)) {
                        // 如果 pivotData 是数组，可能包含 attribute_value_id
                        $realAttributeValueId = $pivotData['attribute_value_id'] ?? $pivotData['id'] ?? null;
                    } elseif (is_numeric($pivotData) && $pivotData > 0) {
                        // 如果 pivotData 是数字且大于 0，可能是 attribute_value_id
                        $realAttributeValueId = $pivotData;
                    }
                    
                    // 如果还是无法获取，尝试从 $ids 的键中查找
                    if (empty($realAttributeValueId) || $realAttributeValueId === 0) {
                        // 遍历 $ids 查找可能的 attribute_value_id
                        foreach ($ids as $key => $value) {
                            if (is_numeric($key) && $key > 0) {
                                $realAttributeValueId = $key;
                                $pivotData = $value; // 更新 pivotData
                                break;
                            }
                        }
                    }
                }
                
                // 如果仍然无法获取有效的 attribute_value_id，记录警告但继续处理
                if (empty($realAttributeValueId) || $realAttributeValueId === 0) {
                    \Illuminate\Support\Facades\Log::warning('ProductAttributeValue 更新同步：无法确定 attribute_value_id，尝试从数据库查询', [
                        'product_id' => $this->id,
                        'original_attribute_value_id' => $attributeValueId,
                        'pivot_data' => $pivotData,
                        'ids_keys' => array_keys($ids),
                        'ids' => $ids,
                    ]);
                }
                
                // 优先从原始 $ids 中获取 attribute_id（最可靠的数据源）
                // 注意：需要处理键的类型转换（字符串 vs 数字）
                $attributeId = null;
                
                // 尝试多种键格式（使用真实的 attribute_value_id）
                $possibleKeys = [
                    $realAttributeValueId,
                    (string) $realAttributeValueId,
                    (int) $realAttributeValueId,
                    $attributeValueId, // 也尝试原始的键
                    (string) $attributeValueId,
                    (int) $attributeValueId,
                ];
                
                foreach ($possibleKeys as $key) {
                    if (isset($ids[$key])) {
                        if (is_array($ids[$key])) {
                            $attributeId = $ids[$key]['attribute_id'] ?? null;
                            break;
                        } elseif (is_numeric($ids[$key])) {
                            // 如果 $ids 的值是数字，可能是 attribute_id
                            $attributeId = $ids[$key];
                            break;
                        }
                    }
                }
                
                // 如果从 $ids 中无法获取，尝试从 sync 返回的 pivotData 中获取
                if ($attributeId === null) {
                    if (is_array($pivotData)) {
                        $attributeId = $pivotData['attribute_id'] ?? null;
                    } elseif (is_numeric($pivotData) && $pivotData > 0) {
                        // 如果 pivotData 是数字且大于 0，可能是 attribute_id（但需要验证）
                        // 这里不直接使用，因为可能是 attribute_value_id
                    }
                }
                
                // 查询 pivot 记录（优先使用 attribute_id，如果查询不到则尝试不限制 attribute_id）
                // 使用真实的 attribute_value_id
                $pivot = null;
                if ($attributeId !== null && $realAttributeValueId > 0) {
                    $pivot = \App\Models\ProductAttributeValue::where([
                        'product_id' => $this->id,
                        'attribute_value_id' => $realAttributeValueId,
                        'attribute_id' => $attributeId,
                    ])->first();
                }
                
                // 如果查询不到，尝试不限制 attribute_id 查询
                if (! $pivot && $realAttributeValueId > 0) {
                    $pivot = \App\Models\ProductAttributeValue::where([
                        'product_id' => $this->id,
                        'attribute_value_id' => $realAttributeValueId,
                    ])->first();
                }
                
                // 如果还是查询不到，尝试从 attribute_value 获取 attribute_id 后重新查询
                $attributeValue = null;
                if (! $pivot && $realAttributeValueId > 0) {
                    $attributeValue = \App\Models\AttributeValue::find($realAttributeValueId);
                    if ($attributeValue && $attributeValue->attribute_id) {
                        // 如果之前没有 attributeId，现在从 attributeValue 获取
                        if ($attributeId === null) {
                            $attributeId = $attributeValue->attribute_id;
                        }
                        
                        $pivot = \App\Models\ProductAttributeValue::where([
                            'product_id' => $this->id,
                            'attribute_value_id' => $realAttributeValueId,
                            'attribute_id' => $attributeValue->attribute_id,
                        ])->first();
                    }
                }
                
                if ($pivot) {
                    $syncService->recordSync($pivot, 'updated', $currentNode);
                } else {
                    // 如果还是查询不到，使用已知数据创建临时 pivot 对象用于同步
                    $finalAttributeId = $attributeId;
                    if ($finalAttributeId === null && $attributeValue) {
                        $finalAttributeId = $attributeValue->attribute_id ?? null;
                    }
                    
                    // 如果还是没有，最后尝试从数据库查询 attribute_value
                    if ($finalAttributeId === null && $realAttributeValueId > 0) {
                        $attributeValue = $attributeValue ?? \App\Models\AttributeValue::find($realAttributeValueId);
                        if ($attributeValue) {
                            $finalAttributeId = $attributeValue->attribute_id ?? null;
                        }
                    }
                    
                    // 如果仍然无法获取 attribute_value_id，尝试从所有已关联的 attribute_values 中查找
                    if (($realAttributeValueId === 0 || empty($realAttributeValueId)) && $finalAttributeId !== null) {
                        // 通过 attribute_id 查找可能的 attribute_value_id
                        $possibleAttributeValue = \App\Models\AttributeValue::where('attribute_id', $finalAttributeId)
                            ->whereHas('products', function ($query) {
                                $query->where('product_id', $this->id);
                            })
                            ->first();
                        
                        if ($possibleAttributeValue) {
                            $realAttributeValueId = $possibleAttributeValue->id;
                        }
                    }
                    
                    // 验证数据完整性：attribute_value_id 和 attribute_id 都必须有效
                    if ($finalAttributeId !== null && $realAttributeValueId > 0) {
                        $pivot = new \App\Models\ProductAttributeValue([
                            'product_id' => $this->id,
                            'attribute_value_id' => $realAttributeValueId,
                            'attribute_id' => $finalAttributeId,
                        ]);
                        $syncService->recordSync($pivot, 'updated', $currentNode);
                    } else {
                        // 最后的后备方案：记录警告日志，但不跳过（尝试使用已知数据）
                        \Illuminate\Support\Facades\Log::warning('ProductAttributeValue 更新同步：无法获取完整数据，尝试使用部分数据', [
                            'product_id' => $this->id,
                            'original_attribute_value_id' => $attributeValueId,
                            'real_attribute_value_id' => $realAttributeValueId,
                            'attribute_id' => $finalAttributeId,
                            'pivot_data' => $pivotData,
                            'pivot_data_type' => gettype($pivotData),
                            'ids_data' => $ids[$realAttributeValueId] ?? $ids[$attributeValueId] ?? null,
                            'ids_keys' => array_keys($ids),
                            'attribute_value_exists' => $attributeValue !== null,
                        ]);
                        
                        // 即使数据不完整，也尝试同步（可能目标节点能处理）
                        if ($finalAttributeId !== null) {
                            // 使用 product_id 和 attribute_id 创建记录（attribute_value_id 为 0）
                            // 这虽然不理想，但至少能同步部分数据
                            $pivot = new \App\Models\ProductAttributeValue([
                                'product_id' => $this->id,
                                'attribute_value_id' => $realAttributeValueId ?: 0, // 如果无法获取，使用 0
                                'attribute_id' => $finalAttributeId,
                            ]);
                            $syncService->recordSync($pivot, 'updated', $currentNode);
                        }
                    }
                }
            }
            
            // 处理删除的记录（使用之前获取的完整记录）
            foreach ($changes['detached'] ?? [] as $attributeValueId) {
                $pivot = $pivotsToDelete->get($attributeValueId);
                if ($pivot) {
                    // 使用完整记录触发同步
                    $syncService->recordSync($pivot, 'deleted', $currentNode);
                } else {
                    // 如果找不到完整记录，尝试从数据库查询（可能是在 sync 之前就被删除了）
                    $pivot = \App\Models\ProductAttributeValue::where('product_id', $this->id)
                        ->where('attribute_value_id', $attributeValueId)
                        ->first();
                    
                    if ($pivot) {
                        // 找到了记录，使用它触发同步
                        $syncService->recordSync($pivot, 'deleted', $currentNode);
                    } else {
                        // 如果还是找不到，尝试通过 attribute_value 查找 attribute_id
                        $attributeValue = \App\Models\AttributeValue::find($attributeValueId);
                        if ($attributeValue && $attributeValue->attribute_id) {
                            // 使用所有已知字段创建记录（用于删除同步）
                            $pivot = new \App\Models\ProductAttributeValue([
                                'product_id' => $this->id,
                                'attribute_value_id' => $attributeValueId,
                                'attribute_id' => $attributeValue->attribute_id,
                            ]);
                            $syncService->recordSync($pivot, 'deleted', $currentNode);
                        } else {
                            // 最后的后备方案：只使用 product_id 和 attribute_value_id
                            // 注意：这可能导致目标节点无法正确删除（如果缺少 attribute_id）
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
                    }
                }
            }
        }
        
        return $changes;
    }

    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category')
            ->using(\App\Models\ProductCategory::class);
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
