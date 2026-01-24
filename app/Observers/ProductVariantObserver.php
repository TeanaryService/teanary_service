<?php

namespace App\Observers;

use App\Models\ProductReview;
use App\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(ProductVariant $productVariant): void
    {
        // 注意：不删除购物车项和订单项，因为它们可能正在使用中
        // 如果确实需要删除，需要先检查状态
        // $productVariant->cartItems()->each(function ($item) {
        //     if (!$item->cart->order) { // 如果购物车没有关联订单
        //         $item->delete();
        //     }
        // });

        // 删除产品变体评价（使用 product_variants 字段）
        ProductReview::where('product_variants', $productVariant->id)->each(function ($review) {
            $review->delete();
        });

        // 删除中间表关联（产品变体-规格值）
        $productVariant->specificationValues()->detach();

        // 删除中间表关联（产品变体-促销）
        $productVariant->promotions()->detach();
    }

    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $productVariant): void
    {
        //
    }
}
