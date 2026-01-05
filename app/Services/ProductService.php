<?php

namespace App\Services;

use App\Enums\ProductStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
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
            'source_url' => $data['source_url'] ?? null,
            'status' => ProductStatusEnum::Inactive,
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

        // 处理商品属性
        if (!empty($data['attributes'])) {
            $languageId = $data['translations'][0]['language_id'] ?? null;
            if ($languageId) {
                $this->syncProductAttributes($product, $data['attributes'], $languageId);
            }
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

    /**
     * 同步商品属性
     *
     * @param Product $product
     * @param array $attributesData
     * @param int $languageId
     * @return void
     */
    protected function syncProductAttributes(Product $product, array $attributesData, int $languageId): void
    {
        $syncData = [];

        foreach ($attributesData as $attrData) {
            $attributeName = $attrData['name'] ?? '';
            $attributeValueName = $attrData['value'] ?? '';

            if (empty($attributeName) || empty($attributeValueName)) {
                continue;
            }

            // 查找或创建属性
            $attribute = $this->findOrCreateAttribute($attributeName, $languageId);

            // 查找或创建属性值
            $attributeValue = $this->findOrCreateAttributeValue($attribute, $attributeValueName, $languageId);

            // 准备同步数据（使用 attribute_value_id 作为键，attribute_id 作为 pivot 数据）
            $syncData[$attributeValue->id] = [
                'attribute_id' => $attribute->id,
            ];
        }

        // 同步商品属性
        if (!empty($syncData)) {
            $product->attributeValues()->sync($syncData);
        }
    }

    /**
     * 查找或创建属性
     *
     * @param string $name
     * @param int $languageId
     * @return Attribute
     */
    protected function findOrCreateAttribute(string $name, int $languageId): Attribute
    {
        // 先尝试通过翻译查找
        $translation = AttributeTranslation::where('name', $name)
            ->where('language_id', $languageId)
            ->first();

        if ($translation) {
            return $translation->attribute;
        }

        // 如果不存在，创建新属性
        $attribute = Attribute::create([]);
        $attribute->attributeTranslations()->create([
            'language_id' => $languageId,
            'name' => $name,
        ]);

        return $attribute;
    }

    /**
     * 查找或创建属性值
     *
     * @param Attribute $attribute
     * @param string $valueName
     * @param int $languageId
     * @return AttributeValue
     */
    protected function findOrCreateAttributeValue(Attribute $attribute, string $valueName, int $languageId): AttributeValue
    {
        // 先尝试通过翻译查找该属性下的属性值
        $translation = AttributeValueTranslation::whereHas('attributeValue', function ($query) use ($attribute) {
            $query->where('attribute_id', $attribute->id);
        })
            ->where('name', $valueName)
            ->where('language_id', $languageId)
            ->first();

        if ($translation) {
            return $translation->attributeValue;
        }

        // 如果不存在，创建新属性值
        $attributeValue = AttributeValue::create([
            'attribute_id' => $attribute->id,
        ]);
        $attributeValue->attributeValueTranslations()->create([
            'language_id' => $languageId,
            'name' => $valueName,
        ]);

        return $attributeValue;
    }
}

