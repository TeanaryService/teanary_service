<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;

class ProductService
{
    public function __construct(
        protected MediaService $mediaService,
        protected CategoryService $categoryService
    ) {
    }

    /**
     * 创建商品
     *
     * @param array $data
     * @return Product
     */
    public function createProduct(array $data): Product
    {
        $product = Product::create([
            'slug' => $data['slug'],
        ]);

        // 处理主图
        if (isset($data['main_image'])) {
            $this->mediaService->handleMainImage($product, $data['main_image']);
        }

        // 处理内容图片
        $imageMap = $this->mediaService->handleContentImages($product, $data['content_images'] ?? null);

        // 处理分类
        if (!empty($data['categories'])) {
            $categoryIds = $this->categoryService->findOrCreateCategories($data['categories']);
            if (!empty($categoryIds)) {
                $product->productCategories()->syncWithoutDetaching($categoryIds);
            }
        }

        // 处理商品翻译内容
        $this->createProductTranslations($product, $data['translations'], $imageMap);

        // 处理商品规格
        if (!empty($data['variants'])) {
            $this->createProductVariants($product, $data['variants']);
        }

        // 触发搜索索引
        $product->searchable();

        return $product->load([
            'productTranslations',
            'media',
            'productCategories',
            'productVariants.specificationValues',
        ]);
    }

    /**
     * 创建商品规格
     *
     * @param Product $product
     * @param array $variantsData
     * @return void
     */
    protected function createProductVariants(Product $product, array $variantsData): void
    {
        foreach ($variantsData as $variantData) {
            $variant = $this->createVariant($product, $variantData);
            
            // 关联规格值
            if (!empty($variantData['specification_values'])) {
                $this->syncVariantSpecificationValues($variant, $variantData['specification_values']);
            }
        }
    }

    /**
     * 创建单个商品规格
     *
     * @param Product $product
     * @param array $variantData
     * @return ProductVariant
     */
    protected function createVariant(Product $product, array $variantData): ProductVariant
    {
        return $product->productVariants()->create([
            'sku' => $variantData['sku'],
            'price' => $variantData['price'] ?? null,
            'cost' => $variantData['cost'] ?? null,
            'stock' => $variantData['stock'] ?? 0,
            'weight' => $variantData['weight'] ?? null,
            'length' => $variantData['length'] ?? null,
            'width' => $variantData['width'] ?? null,
            'height' => $variantData['height'] ?? null,
        ]);
    }

    /**
     * 同步规格值关联
     *
     * @param ProductVariant $variant
     * @param array $specificationValues
     * @return void
     */
    protected function syncVariantSpecificationValues(ProductVariant $variant, array $specificationValues): void
    {
        $syncData = [];
        foreach ($specificationValues as $specValue) {
            $syncData[$specValue['specification_value_id']] = [
                'specification_id' => $specValue['specification_id'],
            ];
        }
        $variant->specificationValues()->sync($syncData);
    }

    /**
     * 创建商品翻译
     *
     * @param Product $product
     * @param array $translations
     * @param array $imageMap
     * @return void
     */
    protected function createProductTranslations(Product $product, array $translations, array $imageMap): void
    {
        foreach ($translations as $translation) {
            $description = $this->mediaService->replaceImagePlaceholders(
                $translation['description'] ?? null,
                $imageMap
            );

            $product->productTranslations()->create([
                'language_id' => $translation['language_id'],
                'name' => $translation['name'],
                'description' => $description,
                'short_description' => $translation['short_description'] ?? null,
            ]);
        }
    }
}

