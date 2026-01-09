<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\CascadesMediaDeletes;
use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class ProductVariant.
 *
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property float|null $price
 * @property float|null $cost
 * @property int $stock
 * @property float|null $weight
 * @property float|null $length
 * @property float|null $width
 * @property float|null $height
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Product $product
 * @property Collection|CartItem[] $cartItems
 * @property Collection|OrderItem[] $orderItems
 * @property Collection|Specification[] $specifications
 * @property Collection|SpecificationValue[] $specificationValues
 * @property Collection|Promotion[] $promotions
 */
class ProductVariant extends Model implements HasMedia
{
    use CascadesMediaDeletes;
    use HasFactory;
    use HasSnowflakeId;
    use InteractsWithMedia;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'product_id' => 'int',
        'price' => 'float',
        'cost' => 'float',
        'stock' => 'int',
        'weight' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float',
    ];

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'cost',
        'stock',
        'weight',
        'length',
        'width',
        'height',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

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

    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class, 'product_variant_specification_value')
            ->withPivot('specification_value_id')
            ->using(\App\Models\ProductVariantSpecificationValue::class);
    }

    public function specificationValues(): BelongsToMany
    {
        return $this->belongsToMany(SpecificationValue::class, 'product_variant_specification_value')
            ->withPivot('specification_id')
            ->using(\App\Models\ProductVariantSpecificationValue::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product_variant')
            ->withPivot(['product_id', 'product_variant_id', 'promotion_id'])
            ->using(\App\Models\PromotionProductVariant::class);
    }

    /**
     * 同步规格值并触发同步.
     */
    public function syncSpecificationValues(array $ids, bool $detaching = true): array
    {
        // 在同步之前，先获取要删除的记录（用于同步）
        $pivotsToDelete = [];
        if (config('sync.enabled') && $detaching) {
            // 获取当前已关联的规格值ID
            $currentSpecificationValueIds = $this->specificationValues()->pluck('specification_value_id')->toArray();
            // 计算要删除的规格值ID
            $idsToKeep = array_keys($ids);
            $idsToDelete = array_diff($currentSpecificationValueIds, $idsToKeep);
            
            // 获取要删除的完整记录
            if (!empty($idsToDelete)) {
                $pivotsToDelete = \App\Models\ProductVariantSpecificationValue::where('product_variant_id', $this->id)
                    ->whereIn('specification_value_id', $idsToDelete)
                    ->get()
                    ->keyBy('specification_value_id');
            }
        }
        
        $changes = $this->specificationValues()->sync($ids, $detaching);
        
        // 手动触发 Pivot 模型的同步
        if (config('sync.enabled')) {
            $syncService = app(\App\Services\SyncService::class);
            $currentNode = config('sync.node');
            
            // 处理新增的记录
            foreach ($changes['attached'] ?? [] as $specificationValueId => $pivotData) {
                $pivot = \App\Models\ProductVariantSpecificationValue::where([
                    'product_variant_id' => $this->id,
                    'specification_value_id' => $specificationValueId,
                    'specification_id' => $pivotData['specification_id'] ?? null,
                ])->first();
                
                if ($pivot) {
                    $syncService->recordSync($pivot, 'created', $currentNode);
                }
            }
            
            // 处理更新的记录
            foreach ($changes['updated'] ?? [] as $specificationValueId => $pivotData) {
                $pivot = \App\Models\ProductVariantSpecificationValue::where([
                    'product_variant_id' => $this->id,
                    'specification_value_id' => $specificationValueId,
                    'specification_id' => $pivotData['specification_id'] ?? null,
                ])->first();
                
                if ($pivot) {
                    $syncService->recordSync($pivot, 'updated', $currentNode);
                }
            }
            
            // 处理删除的记录（使用之前获取的完整记录）
            foreach ($changes['detached'] ?? [] as $specificationValueId) {
                $pivot = $pivotsToDelete->get($specificationValueId);
                if ($pivot) {
                    $syncService->recordSync($pivot, 'deleted', $currentNode);
                } else {
                    // 如果找不到完整记录，使用部分信息创建（作为后备方案）
                    $pivot = new \App\Models\ProductVariantSpecificationValue([
                        'product_variant_id' => $this->id,
                        'specification_value_id' => $specificationValueId,
                    ]);
                    $syncService->recordSync($pivot, 'deleted', $currentNode);
                }
            }
        }
        
        return $changes;
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(350)
            ->height(350)
            ->sharpen(10)
            ->nonOptimized();
    }
}
