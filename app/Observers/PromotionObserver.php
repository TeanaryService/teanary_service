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
     */
    public function deleting(Promotion $promotion): void
    {
        $promotion->promotionRules()->each(function ($rule) {
            $rule->delete();
        });
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
