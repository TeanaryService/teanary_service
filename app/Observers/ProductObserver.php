<?php

namespace App\Observers;

use App\Models\Product;
use App\Traits\HandlesEditorUploads;

class ProductObserver
{
    use HandlesEditorUploads;

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Product $product): void
    {
        // 删除产品变体
        $product->productVariants()->each(function ($variant) {
            $variant->delete();
        });

        // 删除产品评价
        $product->productReviews()->each(function ($review) {
            $review->delete();
        });

        // 删除产品翻译
        $product->productTranslations()->each(function ($translation) {
            // 删除正文文件
            $this->deleteEditorUploadsFromHtml($translation->description);
            $translation->delete();
        });

        // 删除中间表关联（产品-分类）
        $product->productCategories()->detach();

        // 删除中间表关联（产品-属性值）
        $product->attributeValues()->detach();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }
}
