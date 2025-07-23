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
     * Handle the Promotion "deleting" event.
     */
    public function deleting(Product $product): void
    {
        $product->productVariants()->each(function ($variant) {
            $variant->delete();
        });

        $product->productReviews()->each(function ($review) {
            $review->delete();
        });

        //删除正文文件
        $product->productTranslations()->each(function ($translation) {
            $this->deleteEditorUploadsFromHtml($translation->description);
        });
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }
}
