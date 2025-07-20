<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionRule;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PromotionService
{
    /**
     * 计算商品规格的最终支付金额及促销信息
     * @param ProductVariant $variant
     * @param int $qty
     * @param User|null $user
     * @return array ['final_price' => float, 'promotion' => array|null]
     */
    public function calculateVariantPrice(ProductVariant $variant, int $qty = 1, ?User $user = null): array
    {
        $basePrice = $variant->price ?? 0;
        $promotions = $this->getAvailablePromotionsForVariant($variant, $user);

        $finalPrice = $basePrice;
        $appliedPromotion = null;

        foreach ($promotions as $promotion) {
            foreach ($promotion->promotionRules as $rule) {
                if ($this->checkRule($rule, $basePrice, $qty)) {
                    $discount = $this->getDiscountAmount($rule, $basePrice, $qty);
                    $priceAfterDiscount = max(0, $basePrice - $discount);
                    if ($priceAfterDiscount < $finalPrice) {
                        $finalPrice = $priceAfterDiscount;
                        $appliedPromotion = [
                            'id' => $promotion->id,
                            'name' => $promotion->promotionTranslations->first()?->name ?? '',
                            'description' => $promotion->promotionTranslations->first()?->description ?? '',
                            'discount' => $discount,
                            'final_price' => $finalPrice,
                            'rule' => $rule->toArray(),
                        ];
                    }
                }
            }
        }

        return [
            'final_price' => $finalPrice,
            'promotion' => $appliedPromotion,
        ];
    }

    /**
     * 计算订单的最终支付金额及促销信息
     * @param Order $order
     * @return array ['final_total' => float, 'promotion' => array|null]
     */
    public function calculateOrderTotal(Order $order): array
    {
        $baseTotal = $order->orderItems->sum(function ($item) {
            return $item->qty * ($item->price ?? 0);
        });

        $user = $order->user ?? null;
        $promotions = $this->getAvailablePromotionsForOrder($order, $user);

        $finalTotal = $baseTotal;
        $appliedPromotion = null;

        foreach ($promotions as $promotion) {
            foreach ($promotion->promotionRules as $rule) {
                if ($this->checkRule($rule, $baseTotal, $order->orderItems->sum('qty'))) {
                    $discount = $this->getDiscountAmount($rule, $baseTotal, $order->orderItems->sum('qty'));
                    $totalAfterDiscount = max(0, $baseTotal - $discount);
                    if ($totalAfterDiscount < $finalTotal) {
                        $finalTotal = $totalAfterDiscount;
                        $appliedPromotion = [
                            'id' => $promotion->id,
                            'name' => $promotion->promotionTranslations->first()?->name ?? '',
                            'description' => $promotion->promotionTranslations->first()?->description ?? '',
                            'discount' => $discount,
                            'final_total' => $finalTotal,
                            'rule' => $rule->toArray(),
                        ];
                    }
                }
            }
        }

        return [
            'final_total' => $finalTotal,
            'promotion' => $appliedPromotion,
        ];
    }

    /**
     * 获取可用促销信息列表（用于广告），永久缓存
     * @param User|null $user
     * @return Collection
     */
    public function getAvailablePromotions(?User $user = null): Collection
    {
        $now = now();
        $langId = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;
        $userGroupId = $user?->user_group_id;
        $cacheKey = "promotions.available.{$langId}." . ($userGroupId ?? 'all');
        return \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function () use ($user, $userGroupId, $now, $langId) {
            $query = Promotion::with([
                    'promotionTranslations' => function ($q) use ($langId) {
                        $q->where('language_id', $langId);
                    },
                    'promotionRules'
                ])
                ->where('active', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                });

            if ($user) {
                $query->where(function ($q) use ($userGroupId) {
                    $q->whereDoesntHave('userGroups')
                      ->orWhereHas('userGroups', function ($q2) use ($userGroupId) {
                          $q2->where('id', $userGroupId);
                      });
                });
            }

            return $query->get()->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'name' => $promotion->promotionTranslations->first()?->name ?? '',
                    'description' => $promotion->promotionTranslations->first()?->description ?? '',
                    'type' => $promotion->type->value ?? '',
                    'starts_at' => $promotion->starts_at,
                    'ends_at' => $promotion->ends_at,
                    'rules' => $promotion->promotionRules->map(function ($rule) {
                        return $rule->toArray();
                    })->values(),
                ];
            });
        });
    }

    /**
     * 获取商品规格可用促销，永久缓存
     */
    public function getAvailablePromotionsForVariant(ProductVariant $variant, ?User $user = null): Collection
    {
        $now = now();
        $langId = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;
        $userGroupId = $user?->user_group_id;
        $cacheKey = "promotions.variant.{$variant->id}.{$langId}." . ($userGroupId ?? 'all');
        return \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function () use ($variant, $user, $userGroupId, $now, $langId) {
            $query = Promotion::with([
                    'promotionRules',
                    'promotionTranslations' => function ($q) use ($langId) {
                        $q->where('language_id', $langId);
                    }
                ])
                ->where('active', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                })
                ->whereHas('productVariants', function ($q) use ($variant) {
                    $q->where('product_variant_id', $variant->id);
                });

            if ($user) {
                $query->where(function ($q) use ($userGroupId) {
                    $q->whereDoesntHave('userGroups')
                      ->orWhereHas('userGroups', function ($q2) use ($userGroupId) {
                          $q2->where('id', $userGroupId);
                      });
                });
            }

            return $query->get();
        });
    }

    /**
     * 获取订单可用促销，永久缓存
     */
    public function getAvailablePromotionsForOrder(Order $order, ?User $user = null): Collection
    {
        $now = now();
        $langId = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;
        $userGroupId = $user?->user_group_id;
        $cacheKey = "promotions.order.{$order->id}.{$langId}." . ($userGroupId ?? 'all');
        return \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function () use ($order, $user, $userGroupId, $now, $langId) {
            $query = Promotion::with([
                    'promotionRules',
                    'promotionTranslations' => function ($q) use ($langId) {
                        $q->where('language_id', $langId);
                    }
                ])
                ->where('active', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                });

            if ($user) {
                $query->where(function ($q) use ($userGroupId) {
                    $q->whereDoesntHave('userGroups')
                      ->orWhereHas('userGroups', function ($q2) use ($userGroupId) {
                          $q2->where('id', $userGroupId);
                      });
                });
            }

            return $query->get();
        });
    }

    /**
     * 清理所有促销相关缓存（在促销变动时调用）
     */
    public static function clearPromotionCache()
    {
        Cache::tags(['promotion'])->flush();
        // 或者遍历删除所有相关key
        // Cache::forget('promotions.available.all');
        // Cache::forget('promotions.variant.*');
        // Cache::forget('promotions.order.*');
    }

    /**
     * 判断促销规则是否满足
     */
    protected function checkRule(PromotionRule $rule, float $base, int $qty): bool
    {
        switch ($rule->condition_type->value ?? $rule->condition_type) {
            case 'amount':
                return $base >= $rule->condition_value;
            case 'qty':
                return $qty >= $rule->condition_value;
            default:
                return false;
        }
    }

    /**
     * 计算促销规则的优惠金额
     */
    protected function getDiscountAmount(PromotionRule $rule, float $base, int $qty): float
    {
        switch ($rule->discount_type->value ?? $rule->discount_type) {
            case 'amount':
                return $rule->discount_value;
            case 'percent':
                return $base * ($rule->discount_value / 100);
            default:
                return 0;
        }
    }
}
