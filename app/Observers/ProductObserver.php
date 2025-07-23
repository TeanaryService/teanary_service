<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
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
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }
}
