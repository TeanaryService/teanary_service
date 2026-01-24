<?php

namespace App\Observers;

use App\Models\Country;

class CountryObserver
{
    /**
     * Handle the Country "created" event.
     */
    public function created(Country $country): void
    {
        //
        $this->clearCountryCache();
    }

    /**
     * Handle the Country "updated" event.
     */
    public function updated(Country $country): void
    {
        //
        $this->clearCountryCache();
    }

    /**
     * Handle the Country "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Country $country): void
    {
        // 删除所有关联的地区（会触发 Zone 的 deleting 事件）
        $country->zones()->each(function ($zone) {
            $zone->delete();
        });

        // 删除国家翻译
        $country->countryTranslations()->each(function ($translation) {
            $translation->delete();
        });
    }

    /**
     * Handle the Country "deleted" event.
     */
    public function deleted(Country $country): void
    {
        //
        $this->clearCountryCache();
    }

    /**
     * 清除所有语言下的国家缓存.
     */
    protected function clearCountryCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('countries.with.translations');
    }
}
