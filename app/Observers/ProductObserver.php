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
        // 删除产品变体（会触发 ProductVariant 的 deleted 事件，从而触发同步）
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
        // 先获取要删除的记录，然后手动触发同步，最后删除
        if (config('sync.enabled')) {
            $syncService = app(\App\Services\SyncService::class);
            $currentNode = config('sync.node');
            
            // 获取所有产品分类关联
            $productCategories = \App\Models\ProductCategory::where('product_id', $product->id)->get();
            foreach ($productCategories as $pivot) {
                $syncService->recordSync($pivot, 'deleted', $currentNode);
            }
        }
        $product->productCategories()->detach();

        // 删除中间表关联（产品-属性值）
        // 先获取要删除的记录，然后手动触发同步，最后删除
        if (config('sync.enabled')) {
            $syncService = app(\App\Services\SyncService::class);
            $currentNode = config('sync.node');
            
            // 获取所有产品属性值关联
            $productAttributeValues = \App\Models\ProductAttributeValue::where('product_id', $product->id)->get();
            foreach ($productAttributeValues as $pivot) {
                $syncService->recordSync($pivot, 'deleted', $currentNode);
            }
        }
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
