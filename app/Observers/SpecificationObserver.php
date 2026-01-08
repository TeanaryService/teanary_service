<?php

namespace App\Observers;

use App\Models\Specification;

class SpecificationObserver
{
    /**
     * Handle the Specification "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Specification $specification): void
    {
        // 删除规格值（会级联删除规格值翻译）
        $specification->specificationValues()->each(function ($value) {
            $value->delete();
        });

        // 删除规格翻译
        $specification->specificationTranslations()->each(function ($translation) {
            $translation->delete();
        });

        // 删除中间表关联（产品变体-规格值）
        $specification->productVariants()->detach();
    }
}
