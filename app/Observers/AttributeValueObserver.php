<?php

namespace App\Observers;

use App\Models\AttributeValue;
use Illuminate\Support\Facades\Cache;

class AttributeValueObserver
{
    /**
     * 清除所有语言下的属性缓存.
     */
    protected function clearAttributeCache(): void
    {
        Cache::forget('attributes.with.translations');
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
     * Handle the AttributeValue "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(AttributeValue $attributeValue): void
    {
        // 删除属性值翻译
        $attributeValue->attributeValueTranslations()->each(function ($translation) {
            $translation->delete();
        });

        // 删除中间表关联（产品-属性值）
        $attributeValue->products()->detach();
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
