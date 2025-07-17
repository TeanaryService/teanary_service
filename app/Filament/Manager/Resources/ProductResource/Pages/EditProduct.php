<?php

namespace App\Filament\Manager\Resources\ProductResource\Pages;

use App\Filament\Manager\Resources\ProductResource;
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

        $record->update($data);

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

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 转换 translations 为嵌套数组
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
