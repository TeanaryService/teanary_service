<?php

namespace App\Filament\Resources\SpecificationResource\Pages;

use App\Filament\Resources\SpecificationResource;
use App\Models\Specification;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecification extends EditRecord
{
    protected static string $resource = SpecificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Specification
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->specificationTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                ['name' => $fields['name'] ?? '']
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translations = [];
        if (isset($this->record->specificationTranslations)) {
            foreach ($this->record->specificationTranslations as $translation) {
                $translations[$translation->language_id] = [
                    'name' => $translation->name,
                ];
            }
        }
        $data['translations'] = $translations;
        return $data;
    }
}
