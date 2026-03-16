<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\PromotionTypeEnum;
use App\Enums\TranslationStatusEnum;
use App\Observers\PromotionObserver;
use App\Services\SyncService;
use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Promotion.
 *
 * @property int $id
 * @property string $type
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|ProductVariant[] $productVariants
 * @property Collection|PromotionRule[] $promotionRules
 * @property Collection|PromotionTranslation[] $promotionTranslations
 * @property Collection|UserGroup[] $userGroups
 */
#[ObservedBy([PromotionObserver::class])]

class Promotion extends Model
{
    use HasFactory;
    use HasSnowflakeId;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'bool',
        'type' => PromotionTypeEnum::class,
        'translation_status' => TranslationStatusEnum::class,
    ];

    protected $fillable = [
        'type',
        'starts_at',
        'ends_at',
        'active',
        'translation_status',
    ];

    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'promotion_product_variant')
            ->withPivot(['product_id', 'product_variant_id', 'promotion_id'])
            ->using(PromotionProductVariant::class);
    }

    /**
     * 同步商品变体并触发同步.
     */
    public function syncProductVariants(array $ids, bool $detaching = true): array
    {
        // 获取所有变体的 product_id，构建 pivot 数据
        $pivotData = [];
        if (! empty($ids)) {
            $variants = ProductVariant::whereIn('id', $ids)->get(['id', 'product_id']);
            foreach ($variants as $variant) {
                $pivotData[$variant->id] = ['product_id' => $variant->product_id];
            }
        }

        $changes = $this->productVariants()->sync($pivotData, $detaching);

        // 手动触发 Pivot 模型的同步
        if (config('sync.enabled')) {
            $syncService = app(SyncService::class);
            $currentNode = config('sync.node');

            // 处理新增的记录
            foreach ($changes['attached'] ?? [] as $productVariantId => $pivotData) {
                $pivot = PromotionProductVariant::where([
                    'promotion_id' => $this->id,
                    'product_variant_id' => $productVariantId,
                    'product_id' => $pivotData['product_id'] ?? null,
                ])->first();

                if ($pivot) {
                    $syncService->recordSync($pivot, 'created', $currentNode);
                }
            }

            // 处理更新的记录
            foreach ($changes['updated'] ?? [] as $productVariantId => $pivotData) {
                $pivot = PromotionProductVariant::where([
                    'promotion_id' => $this->id,
                    'product_variant_id' => $productVariantId,
                    'product_id' => $pivotData['product_id'] ?? null,
                ])->first();

                if ($pivot) {
                    $syncService->recordSync($pivot, 'updated', $currentNode);
                }
            }

            // 处理删除的记录
            foreach ($changes['detached'] ?? [] as $productVariantId) {
                $pivot = new PromotionProductVariant([
                    'promotion_id' => $this->id,
                    'product_variant_id' => $productVariantId,
                ]);
                $syncService->recordSync($pivot, 'deleted', $currentNode);
            }
        }

        return $changes;
    }

    /**
     * 附加商品变体并触发同步.
     */
    public function attachProductVariant(int $productVariantId, array $pivotData = []): void
    {
        // 如果没有提供 product_id，自动获取
        if (! isset($pivotData['product_id'])) {
            $variant = ProductVariant::find($productVariantId);
            if ($variant) {
                $pivotData['product_id'] = $variant->product_id;
            }
        }

        $this->productVariants()->attach($productVariantId, $pivotData);

        // 手动触发 Pivot 模型的同步
        if (config('sync.enabled')) {
            $syncService = app(SyncService::class);
            $currentNode = config('sync.node');

            $pivot = PromotionProductVariant::where([
                'promotion_id' => $this->id,
                'product_variant_id' => $productVariantId,
                'product_id' => $pivotData['product_id'] ?? null,
            ])->first();

            if ($pivot) {
                $syncService->recordSync($pivot, 'created', $currentNode);
            }
        }
    }

    /**
     * 分离商品变体并触发同步.
     */
    public function detachProductVariant($productVariantIds): void
    {
        $ids = is_array($productVariantIds) ? $productVariantIds : [$productVariantIds];

        // 在删除前获取要删除的记录
        $pivotsToDelete = PromotionProductVariant::where('promotion_id', $this->id)
            ->whereIn('product_variant_id', $ids)
            ->get();

        $this->productVariants()->detach($ids);

        // 手动触发 Pivot 模型的同步
        if (config('sync.enabled')) {
            $syncService = app(SyncService::class);
            $currentNode = config('sync.node');

            foreach ($pivotsToDelete as $pivot) {
                $syncService->recordSync($pivot, 'deleted', $currentNode);
            }
        }
    }

    public function promotionRules(): HasMany
    {
        return $this->hasMany(PromotionRule::class);
    }

    public function promotionTranslations(): HasMany
    {
        return $this->hasMany(PromotionTranslation::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class)
            ->using(PromotionUserGroup::class);
    }
}
