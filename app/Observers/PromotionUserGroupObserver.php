<?php

namespace App\Observers;

use App\Models\PromotionUserGroup;
use App\Services\PromotionService;

class PromotionUserGroupObserver
{
    /**
     * Handle the PromotionUserGroup "created" event.
     */
    public function created(PromotionUserGroup $promotionUserGroup): void
    {
        //
        PromotionService::clearPromotionCache();
    }

    /**
     * Handle the PromotionUserGroup "updated" event.
     */
    public function updated(PromotionUserGroup $promotionUserGroup): void
    {
        //
        PromotionService::clearPromotionCache();
    }

    /**
     * Handle the PromotionUserGroup "deleted" event.
     */
    public function deleted(PromotionUserGroup $promotionUserGroup): void
    {
        //
        PromotionService::clearPromotionCache();
    }
}
