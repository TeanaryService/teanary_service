<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordUpdate($record, array $data): Product
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $attributeValues = $data['attributeValues'] ?? [];
        unset($data['attributeValues']);

        $productCategories = $data['productCategories'] ?? [];
        unset($data['productCategories']);

        $record->update($data);

        // 处理多语言
        foreach ($translations as $languageId => $fields) {
            $record->productTranslations()->updateOrCreate(
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

            $record->attributeValues()->sync($pivotData);
        } else {
            $record->attributeValues()->sync([]);
        }

        // 处理分类
        if (! empty($productCategories)) {
            $ids = collect($productCategories)
                ->filter(fn ($item) => ! empty($item['category_id']))
                ->pluck('category_id')
                ->unique()
                ->values()
                ->toArray();
            $record->productCategories()->sync($ids);
        } else {
            $record->productCategories()->sync([]);
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 回填属性值
        $data['attributeValues'] = [];
        if ($this->record && $this->record->attributeValues) {
            foreach ($this->record->attributeValues as $av) {
                $pivot = $av->pivot ?? null;
                $data['attributeValues'][] = [
                    'attribute_id' => $pivot->attribute_id ?? $av->attribute_id,
                    'attribute_value_id' => $av->id,
                ];
            }
        }
        // 回填分类
        $data['productCategories'] = [];
        if ($this->record && $this->record->productCategories) {
            foreach ($this->record->productCategories as $cat) {
                $data['productCategories'][] = [
                    'category_id' => $cat->id,
                ];
            }
        }
        // 回填多语言
        $translations = [];
        if (isset($this->record->productTranslations)) {
            foreach ($this->record->productTranslations as $translation) {
                $translations[$translation->language_id] = [
                    'name' => $translation->name,
                    'description' => $translation->description,
                    'short_description' => $translation->short_description,
                ];
            }
        }
        $data['translations'] = $translations;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
