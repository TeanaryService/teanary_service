<?php

namespace App\Observers;

use App\Models\AttributeValue;

class AttributeValueObserver
{
    /**
     * 清除所有语言下的属性缓存
     */
    protected function clearAttributeCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('attributes.with.translations');
    }

    /**
     * Handle the AttributeValue "created" event.
     */
    public function created(AttributeValue $attributeValue): void
    {
        //
        $this->clearAttributeCache();
    }

    /**
     * Handle the AttributeValue "updated" event.
     */
    public function updated(AttributeValue $attributeValue): void
    {
        //
        $this->clearAttributeCache();
    }

    /**
     * Handle the AttributeValue "deleted" event.
     */
    public function deleted(AttributeValue $attributeValue): void
    {
        //
        $this->clearAttributeCache();
    }
}
