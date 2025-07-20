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
    protected function getPromotionFromCache($promotionId)
    {
        // 从缓存获取所有促销模型对象
        $allPromotions = static::getAllPromotionsCached();
        return $allPromotions->firstWhere('id', $promotionId);
    }

    public function calculateVariantPrice(ProductVariant $variant, int $qty = 1, ?User $user = null): array
    {
        $basePrice = $variant->price ?? 0;
        $promotions = $this->getAvailablePromotionsForVariant($variant, $user);

        $finalPrice = $basePrice;
        $appliedPromotion = null;
        $langId = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;

        foreach ($promotions as $promotion) {
            // 优先用缓存
            $promotionModel = $this->getPromotionFromCache($promotion['id']);
            if (!$promotionModel) continue;
            foreach ($promotionModel->promotionRules as $rule) {
                if ($this->checkRule($rule, $basePrice, $qty)) {
                    $discount = $this->getDiscountAmount($rule, $basePrice, $qty);
                    $priceAfterDiscount = max(0, $basePrice - $discount);
                    if ($priceAfterDiscount < $finalPrice) {
                        $finalPrice = $priceAfterDiscount;
                        $trans = $promotionModel->promotionTranslations->where('language_id', $langId)->first();
                        $appliedPromotion = [
                            'id' => $promotionModel->id,
                            'name' => $trans?->name ?? $promotionModel->promotionTranslations->first()?->name ?? '',
                            'description' => $trans?->description ?? $promotionModel->promotionTranslations->first()?->description ?? '',
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
        $langId = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;

        foreach ($promotions as $promotion) {
            // 优先用缓存
            $promotionModel = $this->getPromotionFromCache($promotion['id']);
            if (!$promotionModel) continue;
            foreach ($promotionModel->promotionRules as $rule) {
                if ($this->checkRule($rule, $baseTotal, $order->orderItems->sum('qty'))) {
                    $discount = $this->getDiscountAmount($rule, $baseTotal, $order->orderItems->sum('qty'));
                    $totalAfterDiscount = max(0, $baseTotal - $discount);
                    if ($totalAfterDiscount < $finalTotal) {
                        $finalTotal = $totalAfterDiscount;
                        $trans = $promotionModel->promotionTranslations->where('language_id', $langId)->first();
                        $appliedPromotion = [
                            'id' => $promotionModel->id,
                            'name' => $trans?->name ?? $promotionModel->promotionTranslations->first()?->name ?? '',
                            'description' => $trans?->description ?? $promotionModel->promotionTranslations->first()?->description ?? '',
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
     * 总缓存所有促销（含翻译、规则、userGroups），永久缓存
     * @return \Illuminate\Support\Collection
     */
    public static function getAllPromotionsCached()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('promotions.all', function () {
            return Promotion::with([
                'promotionTranslations',
                'promotionRules',
                'userGroups'
            ])->get();
        });
    }

    /**
     * 获取可用促销信息列表（用于广告），从总缓存筛选
     * @param User|null $user
     * @param int|null $langId
     * @return \Illuminate\Support\Collection
     */
    public function getAvailablePromotions(?User $user = null, ?int $langId = null)
    {
        $now = now();
        $langId = $langId ?: app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;
        $userGroupId = $user?->user_group_id;

        return static::getAllPromotionsCached()->filter(function ($promotion) use ($now, $userGroupId) {
            if (!$promotion->active) return false;
            if ($promotion->starts_at && $promotion->starts_at > $now) return false;
            if ($promotion->ends_at && $promotion->ends_at < $now) return false;
            if ($promotion->userGroups->count() > 0 && $userGroupId) {
                return $promotion->userGroups->contains('id', $userGroupId);
            }
            // 向上兼容：无userGroups绑定时所有用户可用
            return true;
        })->map(function ($promotion) use ($langId) {
            $trans = $promotion->promotionTranslations->where('language_id', $langId)->first();
            return [
                'id' => $promotion->id,
                'name' => $trans?->name ?? '',
                'description' => $trans?->description ?? '',
                'type' => $promotion->type->value ?? '',
                'starts_at' => $promotion->starts_at,
                'ends_at' => $promotion->ends_at,
                'rules' => $promotion->promotionRules->map(function ($rule) {
                    return $rule->toArray();
                })->values(),
            ];
        })->values();
    }

    /**
     * 获取商品规格可用促销，从总缓存筛选
     */
    public function getAvailablePromotionsForVariant(ProductVariant $variant, ?User $user = null, ?int $langId = null)
    {
        $promotions = $this->getAvailablePromotions($user, $langId);

        return $promotions->filter(function ($promotion) use ($variant) {
            // 优先用缓存
            $promotionModel = $this->getPromotionFromCache($promotion['id']);
            return $promotionModel && $promotionModel->productVariants->contains(function ($pv) use ($variant) {
                return $pv->pivot->product_variant_id === $variant->id;
            });
        })->values();
    }

    /**
     * 获取订单可用促销，从总缓存筛选
     */
    public function getAvailablePromotionsForOrder(Order $order, ?User $user = null, ?int $langId = null)
    {
        return $this->getAvailablePromotions($user, $langId);
    }

    /**
     * 清理所有促销相关缓存（在促销变动时调用）
     */
    public static function clearPromotionCache()
    {
        \Illuminate\Support\Facades\Cache::forget('promotions.all');
    }

    /**
     * 判断促销规则是否满足
     */
    protected function checkRule(PromotionRule $rule, float $base, int $qty): bool
    {
        // PromotionConditionTypeEnum: order_total_min, order_qty_min
        $type = $rule->condition_type->value;

        switch ($type) {
            case 'order_total_min':
                return $base >= $rule->condition_value;
            case 'order_qty_min':
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
        // PromotionDiscountTypeEnum: fixed, percentage
        $type =  $rule->discount_type->value;
        switch ($type) {
            case 'fixed':
                return $rule->discount_value;
            case 'percentage':
                return $base * ($rule->discount_value / 100);
            default:
                return 0;
        }
    }
}
