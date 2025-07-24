<?php

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use App\Models\AttributeValue;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttributeValue extends EditRecord
{
    protected static string $resource = AttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 转换 translations 为嵌套数组
        $translations = [];
        if (isset($this->record->attributeValueTranslations)) {
            foreach ($this->record->attributeValueTranslations as $translation) {
                $translations[$translation->language_id]['name'] = $translation->name;
            }
        }
        $data['translations'] = $translations;
        return $data;
    }

    protected function handleRecordUpdate($record, array $data): AttributeValue
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->attributeValueTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                ['name' => $fields['name'] ?? '']
            );
        }

        return $record;
    }
}
