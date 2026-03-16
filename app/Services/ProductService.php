<?php

namespace App\Services;

use App\Enums\ProductStatusEnum;
use App\Enums\TranslationStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;

class ProductService
{
    public function __construct(
        protected MediaService $mediaService,
        protected CategoryService $categoryService
    ) {}

    /**
     * 创建商品
     */
    public function createProduct(array $data): Product
    {
        // 处理 source_url，去掉 URL 参数
        $sourceUrl = $data['source_url'] ?? null;
        if ($sourceUrl) {
            $sourceUrl = $this->removeUrlParams($sourceUrl);
        }

        $product = Product::create([
            'slug' => $data['slug'],
            'source_url' => $sourceUrl,
            'status' => ProductStatusEnum::Inactive,
            'translation_status' => TranslationStatusEnum::NotTranslated, // 默认不翻译
        ]);

        // 处理主图（支持单个或数组）
        if (isset($data['main_image'])) {
            $this->mediaService->handleMainImage($product, $data['main_image']);
        } elseif (isset($data['main_images']) && is_array($data['main_images'])) {
            // 支持 main_images 数组格式
            $this->mediaService->handleMainImage($product, $data['main_images']);
        }

        // 处理内容图片
        $imageMap = $this->mediaService->handleContentImages($product, $data['content_images'] ?? null);

        // 处理分类
        if (! empty($data['categories'])) {
            $categoryIds = $this->categoryService->findOrCreateCategories($data['categories']);
            if (! empty($categoryIds)) {
                // 使用 syncWithoutDetaching 然后手动触发同步
                $changes = $product->productCategories()->syncWithoutDetaching($categoryIds);
                if (config('sync.enabled')) {
                    $syncService = app(SyncService::class);
                    $currentNode = config('sync.node');
                    foreach ($changes['attached'] ?? [] as $categoryId) {
                        $pivot = ProductCategory::where([
                            'product_id' => $product->id,
                            'category_id' => $categoryId,
                        ])->first();
                        if ($pivot) {
                            $syncService->recordSync($pivot, 'created', $currentNode);
                        }
                    }
                }
            }
        }

        // 处理商品翻译内容
        $this->createProductTranslations($product, $data['translations'], $imageMap);

        // 处理商品规格
        if (! empty($data['variants'])) {
            $languageId = $data['translations'][0]['language_id'] ?? null;
            $this->createProductVariants($product, $data['variants'], $languageId);
        }

        // 处理商品属性
        if (! empty($data['attributes'])) {
            $attributesData = $this->normalizeAttributesData($data['attributes']);
            $languageId = $data['translations'][0]['language_id'] ?? null;
            if ($languageId && ! empty($attributesData)) {
                $this->syncProductAttributes($product, $attributesData, $languageId);
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
     */
    protected function createProductVariants(Product $product, array $variantsData, ?int $languageId = null): void
    {
        foreach ($variantsData as $variantData) {
            $variant = $this->createVariant($product, $variantData);

            // 关联规格值
            if (! empty($variantData['specification_values'])) {
                $this->syncVariantSpecificationValues($variant, $variantData['specification_values'], $languageId);
            }
        }
    }

    /**
     * 创建单个商品规格
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
     * 同步规格值关联.
     */
    protected function syncVariantSpecificationValues(ProductVariant $variant, array $specificationValues, ?int $languageId = null): void
    {
        $syncData = [];

        // 如果没有传入languageId，尝试从商品翻译中获取
        if (! $languageId) {
            $firstTranslation = $variant->product->productTranslations()->first();
            $languageId = $firstTranslation ? ($firstTranslation->language_id ?? 1) : 1;
        }

        foreach ($specificationValues as $specValue) {
            $specificationName = $specValue['specification_name'] ?? '';
            $specificationValueName = $specValue['specification_value_name'] ?? '';

            if (empty($specificationName) || empty($specificationValueName)) {
                continue;
            }

            // 根据名称查找或创建规格和规格值
            $specification = $this->findOrCreateSpecification($specificationName, $languageId);
            $specificationValue = $this->findOrCreateSpecificationValue(
                $specification,
                $specificationValueName,
                $languageId
            );

            $syncData[$specificationValue->id] = [
                'specification_id' => $specification->id,
            ];
        }

        if (! empty($syncData)) {
            $variant->syncSpecificationValues($syncData);
        } else {
            $variant->syncSpecificationValues([]);
        }
    }

    /**
     * 创建商品翻译.
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
     * 同步商品属性.
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

        // 同步商品属性（使用自定义方法以触发同步）
        if (! empty($syncData)) {
            $product->syncAttributeValues($syncData);
        } else {
            // 如果 syncData 为空，清空所有关联
            $product->syncAttributeValues([]);
        }
    }

    /**
     * 查找或创建属性.
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

    /**
     * 查找或创建规格
     */
    protected function findOrCreateSpecification(string $name, int $languageId): Specification
    {
        // 先尝试通过翻译查找
        $translation = SpecificationTranslation::where('name', $name)
            ->where('language_id', $languageId)
            ->first();

        if ($translation) {
            return $translation->specification;
        }

        // 如果不存在，创建新规格
        $specification = Specification::create([]);
        $specification->specificationTranslations()->create([
            'language_id' => $languageId,
            'name' => $name,
        ]);

        return $specification;
    }

    /**
     * 查找或创建规格值
     */
    protected function findOrCreateSpecificationValue(Specification $specification, string $valueName, int $languageId): SpecificationValue
    {
        // 先尝试通过翻译查找该规格下的规格值
        $translation = SpecificationValueTranslation::whereHas('specificationValue', function ($query) use ($specification) {
            $query->where('specification_id', $specification->id);
        })
            ->where('name', $valueName)
            ->where('language_id', $languageId)
            ->first();

        if ($translation) {
            return $translation->specificationValue;
        }

        // 如果不存在，创建新规格值（使用 withoutEvents 避免 ID 冲突）
        $specificationValue = SpecificationValue::withoutEvents(function () use ($specification) {
            return SpecificationValue::create([
                'specification_id' => $specification->id,
            ]);
        });
        $specificationValue->specificationValueTranslations()->create([
            'language_id' => $languageId,
            'name' => $valueName,
        ]);

        return $specificationValue;
    }

    /**
     * 去掉 URL 中的查询参数.
     */
    protected function removeUrlParams(string $url): string
    {
        $parsed = parse_url($url);

        if ($parsed === false) {
            return $url;
        }

        // 重新构建 URL，只包含 scheme、host、path，去掉 query 和 fragment
        $scheme = $parsed['scheme'] ?? '';
        $user = $parsed['user'] ?? '';
        $pass = $parsed['pass'] ?? '';
        $host = $parsed['host'] ?? '';
        $port = $parsed['port'] ?? '';
        $path = $parsed['path'] ?? '';

        $cleanUrl = '';

        if ($scheme) {
            $cleanUrl .= $scheme.'://';
        }

        if ($user) {
            $cleanUrl .= $user;
            if ($pass) {
                $cleanUrl .= ':'.$pass;
            }
            $cleanUrl .= '@';
        }

        if ($host) {
            $cleanUrl .= $host;
        }

        if ($port) {
            $cleanUrl .= ':'.$port;
        }

        if ($path) {
            $cleanUrl .= $path;
        }

        return $cleanUrl;
    }

    /**
     * 规范化属性数据格式.
     */
    protected function normalizeAttributesData(mixed $attributes): array
    {
        if (is_array($attributes)) {
            return $attributes;
        }

        if (is_object($attributes)) {
            return (array) $attributes;
        }

        return [];
    }
}
