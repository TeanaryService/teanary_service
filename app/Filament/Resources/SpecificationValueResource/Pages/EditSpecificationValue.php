<?php

namespace App\Filament\Resources\SpecificationValueResource\Pages;

use App\Filament\Resources\SpecificationValueResource;
use App\Models\SpecificationValue;
use Filament\Resources\Pages\EditRecord;

class EditSpecificationValue extends EditRecord
{
    protected static string $resource = SpecificationValueResource::class;

    protected function handleRecordUpdate($record, array $data): SpecificationValue
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->specificationValueTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                ['name' => $fields['name'] ?? '']
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translations = [];
        if (isset($this->record->specificationValueTranslations)) {
            foreach ($this->record->specificationValueTranslations as $translation) {
                $translations[$translation->language_id] = [
                    'name' => $translation->name,
                ];
            }
        }
        $data['translations'] = $translations;

        return $data;
    }
}
