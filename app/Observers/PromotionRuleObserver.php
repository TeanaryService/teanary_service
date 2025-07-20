<?php

namespace App\Observers;

use App\Models\PromotionRule;
use App\Services\PromotionService;

class PromotionRuleObserver
{
    /**
     * Handle the PromotionRule "created" event.
     */
    public function created(PromotionRule $promotionRule): void
    {
        //
        PromotionService::clearPromotionCache();
    }

    /**
     * Handle the PromotionRule "updated" event.
     */
    public function updated(PromotionRule $promotionRule): void
    {
        //
        PromotionService::clearPromotionCache();
    }

    /**
     * Handle the PromotionRule "deleted" event.
     */
    public function deleted(PromotionRule $promotionRule): void
    {
        //
        PromotionService::clearPromotionCache();
    }
}
