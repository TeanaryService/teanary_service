<?php

namespace App\Observers;

use App\Models\Attribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\Cache;

class AttributeObserver
{
    /**
     * 清除所有语言下的属性缓存.
     */
    protected function clearAttributeCache(): void
    {
        Cache::forget('attributes.with.translations');
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
        // 通过 attributeValues 找到所有关联的产品属性值，然后删除关联
        $attributeValueIds = $attribute->attributeValues()->pluck('id');
        ProductAttributeValue::whereIn('attribute_value_id', $attributeValueIds)->delete();
    }

    /**
     * Handle the Attribute "deleted" event.
     */
    public function deleted(Attribute $attribute): void
    {
        $this->clearAttributeCache();
    }
}
