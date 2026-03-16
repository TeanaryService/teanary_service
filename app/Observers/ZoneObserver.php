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
        $this->clearZoneCache($zone);
    }

    /**
     * Handle the Zone "updated" event.
     */
    public function updated(Zone $zone): void
    {
        //
        $this->clearZoneCache($zone);
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
        $this->clearZoneCache($zone);
    }

    /**
     * 清除该地区所属国家的地区缓存（按国家缓存，只清除受影响国家）.
     */
    protected function clearZoneCache(Zone $zone): void
    {
        Zone::clearZoneCacheForCountry($zone->country_id);
    }
}
