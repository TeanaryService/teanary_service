<?php

namespace App\Observers;

use App\Models\Attribute;

class AttributeObserver
{
    /**
     * 清除所有语言下的属性缓存
     */
    protected function clearAttributeCache(): void
    {
        $languages = app(\App\Services\LocaleCurrencyService::class)->getLanguages();
        foreach ($languages as $lang) {
            \Illuminate\Support\Facades\Cache::forget("attributes.with.translations.{$lang->id}");
        }
    }

    /**
     * Handle the Attribute "created" event.
     */
    public function created(Attribute $attribute): void
    {
        $this->clearAttributeCache();
    }

    /**
     * Handle the Attribute "updated" event.
     */
    public function updated(Attribute $attribute): void
    {
        $this->clearAttributeCache();
    }

    /**
     * Handle the Attribute "deleted" event.
     */
    public function deleted(Attribute $attribute): void
    {
        $this->clearAttributeCache();
    }

}
