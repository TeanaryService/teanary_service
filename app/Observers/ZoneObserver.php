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
     * Handle the Zone "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Zone $zone): void
    {
        // 注意：不删除地址，因为地址可能被订单引用
        // 如果确实需要删除，需要先检查是否有订单关联
        // $zone->addresses()->each(function ($address) {
        //     if (!$address->orders()->exists()) {
        //         $address->delete();
        //     }
        // });

        // 删除地区翻译
        $zone->zoneTranslations()->each(function ($translation) {
            $translation->delete();
        });
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
