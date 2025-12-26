<?php

namespace App\Observers;

use App\Models\Zone;

class ZoneObserver
{
    /**
     * Handle the Zone "created" event.
     */
    public function created(Zone $zone): void
    {
        //
        $this->clearZoneCache();
    }

    /**
     * Handle the Zone "updated" event.
     */
    public function updated(Zone $zone): void
    {
        //
        $this->clearZoneCache();
    }

    /**
     * Handle the Zone "deleted" event.
     */
    public function deleted(Zone $zone): void
    {
        //
        $this->clearZoneCache();
    }

    /**
     * 清除所有语言下的地区缓存.
     */
    protected function clearZoneCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('zones.with.translations');
    }
}
