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

        // 处理 source_url，去掉 URL 参数
        if (isset($data['source_url']) && ! empty($data['source_url'])) {
            $data['source_url'] = $this->removeUrlParams($data['source_url']);
        }

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

            $record->syncAttributeValues($pivotData);
        } else {
            $record->syncAttributeValues([]);
        }

        // 处理分类
        if (! empty($productCategories)) {
            $ids = collect($productCategories)
                ->filter(fn ($item) => ! empty($item['category_id']))
                ->pluck('category_id')
                ->unique()
                ->values()
                ->toArray();
            $record->syncProductCategories($ids);
        } else {
            $record->syncProductCategories([]);
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 回填属性值
        $data['attributeValues'] = [];
        if ($this->record) {
            // 确保加载 pivot 数据
            $attributeValues = $this->record->attributeValues()->withPivot('attribute_id')->get();
            foreach ($attributeValues as $av) {
                $pivot = $av->pivot;
                $attributeId = $pivot && isset($pivot->attribute_id) ? $pivot->attribute_id : $av->attribute_id;
                $data['attributeValues'][] = [
                    // 确保ID是字符串类型，以便在Select中正确匹配
                    'attribute_id' => (string) $attributeId,
                    'attribute_value_id' => (string) $av->id,
                ];
            }
        }
        // 回填分类
        $data['productCategories'] = [];
        if ($this->record && $this->record->productCategories) {
            foreach ($this->record->productCategories as $cat) {
                $data['productCategories'][] = [
                    // 确保ID是字符串类型，以便在Select中正确匹配
                    'category_id' => (string) $cat->id,
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

    /**
     * 去掉 URL 中的查询参数.
     */
    protected function removeUrlParams(string $url): string
    {
        $parsed = parse_url($url);

        if ($parsed === false) {
            return $url; // 如果解析失败，返回原 URL
        }

        // 重新构建 URL，只包含 scheme、host、path，去掉 query 和 fragment
        $cleanUrl = '';

        if (isset($parsed['scheme'])) {
            $cleanUrl .= $parsed['scheme'].'://';
        }

        if (isset($parsed['user'])) {
            $cleanUrl .= $parsed['user'];
            if (isset($parsed['pass'])) {
                $cleanUrl .= ':'.$parsed['pass'];
            }
            $cleanUrl .= '@';
        }

        if (isset($parsed['host'])) {
            $cleanUrl .= $parsed['host'];
        }

        if (isset($parsed['port'])) {
            $cleanUrl .= ':'.$parsed['port'];
        }

        if (isset($parsed['path'])) {
            $cleanUrl .= $parsed['path'];
        }

        return $cleanUrl;
    }
}
