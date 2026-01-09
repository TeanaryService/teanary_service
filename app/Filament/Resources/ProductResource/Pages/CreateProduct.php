<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Product
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $attributeValues = $data['attributeValues'] ?? [];
        unset($data['attributeValues']);

        $productCategories = $data['productCategories'] ?? [];
        unset($data['productCategories']);

        $product = static::getModel()::create($data);

        // 处理多语言
        foreach ($translations as $languageId => $fields) {
            $product->productTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                [
                    'name' => $fields['name'] ?? '',
                    'description' => $fields['description'] ?? '',
                    'short_description' => $fields['short_description'] ?? '',
                ]
            );
        }

        // 处理属性值
        if (! empty($attributeValues)) {
            $pivotData = collect($attributeValues)
                ->filter(fn ($item) => ! empty($item['attribute_value_id']) && ! empty($item['attribute_id']))
                ->mapWithKeys(function ($item) {
                    return [
                        $item['attribute_value_id'] => ['attribute_id' => $item['attribute_id']],
                    ];
                })
                ->toArray();

            $product->syncAttributeValues($pivotData);
        }

        // 处理分类
        if (! empty($productCategories)) {
            $ids = collect($productCategories)
                ->filter(fn ($item) => ! empty($item['category_id']))
                ->pluck('category_id')
                ->unique()
                ->values()
                ->toArray();
            $product->syncProductCategories($ids);
        }

        return $product;
    }
}
