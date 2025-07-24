<?php

namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use App\Models\Country;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCountry extends EditRecord
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Country
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->countryTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                ['name' => $fields['name'] ?? '']
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translations = [];
        if (isset($this->record->countryTranslations)) {
            foreach ($this->record->countryTranslations as $translation) {
                $translations[$translation->language_id] = [
                    'name' => $translation->name,
                ];
            }
        }
        $data['translations'] = $translations;
        return $data;
    }
}
