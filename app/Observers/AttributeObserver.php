<?php

namespace App\Observers;

use App\Models\Attribute;

class AttributeObserver
{
    /**
     * 清除所有语言下的属性缓存.
     */
    protected function clearAttributeCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('attributes.with.translations');
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
     * Handle the Attribute "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Attribute $attribute): void
    {
        // 删除属性值（会级联删除属性值翻译）
        $attribute->attributeValues()->each(function ($value) {
            $value->delete();
        });

        // 删除属性翻译
        $attribute->attributeTranslations()->each(function ($translation) {
            $translation->delete();
        });

        // 删除中间表关联（产品-属性值）
        $attribute->products()->detach();
    }

    /**
     * Handle the Attribute "deleted" event.
     */
    public function deleted(Attribute $attribute): void
    {
        $this->clearAttributeCache();
    }
}
