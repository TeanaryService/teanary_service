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
