<?php

namespace App\Observers;

use App\Models\SpecificationValue;

class SpecificationValueObserver
{
    /**
     * Handle the SpecificationValue "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(SpecificationValue $specificationValue): void
    {
        // 删除规格值翻译
        $specificationValue->specificationValueTranslations()->each(function ($translation) {
            $translation->delete();
        });

        // 删除中间表关联（产品变体-规格值）
        $specificationValue->productVariants()->detach();
    }
}
