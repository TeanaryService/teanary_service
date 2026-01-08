<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Services\PromotionService;

class PromotionObserver
{
    /**
     * Handle the Promotion "created" event.
     */
    public function created(Promotion $promotion): void
    {
        //
        PromotionService::clearPromotionCache();
    }

    /**
     * Handle the Promotion "updated" event.
     */
    public function updated(Promotion $promotion): void
    {
        //
        PromotionService::clearPromotionCache();
    }

    /**
     * Handle the Promotion "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Promotion $promotion): void
    {
        // 删除促销规则
        $promotion->promotionRules()->each(function ($rule) {
            $rule->delete();
        });

        // 删除促销翻译
        $promotion->promotionTranslations()->each(function ($translation) {
            $translation->delete();
        });

        // 删除中间表关联（促销-用户组）
        $promotion->userGroups()->detach();

        // 删除中间表关联（促销-产品变体）
        $promotion->productVariants()->detach();
    }

    /**
     * Handle the Promotion "deleted" event.
     */
    public function deleted(Promotion $promotion): void
    {
        //
        PromotionService::clearPromotionCache();
    }
}
