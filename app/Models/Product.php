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
        
        $changes = $this->attributeValues()->sync($ids, $detaching);
        
        // 手动触发 Pivot 模型的同步
        if (config('sync.enabled')) {
            $syncService = app(\App\Services\SyncService::class);
            $currentNode = config('sync.node');
            
            // 处理新增的记录
            foreach ($changes['attached'] ?? [] as $attributeValueId => $pivotData) {
                $pivot = \App\Models\ProductAttributeValue::where([
                    'product_id' => $this->id,
                    'attribute_value_id' => $attributeValueId,
                    'attribute_id' => $pivotData['attribute_id'] ?? null,
                ])->first();
                
                if ($pivot) {
                    $syncService->recordSync($pivot, 'created', $currentNode);
                }
            }
            
            // 处理更新的记录
            foreach ($changes['updated'] ?? [] as $attributeValueId => $pivotData) {
                $pivot = \App\Models\ProductAttributeValue::where([
                    'product_id' => $this->id,
                    'attribute_value_id' => $attributeValueId,
                    'attribute_id' => $pivotData['attribute_id'] ?? null,
                ])->first();
                
                if ($pivot) {
                    $syncService->recordSync($pivot, 'updated', $currentNode);
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
