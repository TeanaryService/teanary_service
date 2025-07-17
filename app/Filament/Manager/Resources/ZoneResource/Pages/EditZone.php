<?php

namespace App\Filament\Manager\Resources\ZoneResource\Pages;

use App\Filament\Manager\Resources\ZoneResource;
use App\Models\Zone;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditZone extends EditRecord
{
    protected static string $resource = ZoneResource::class;

    protected function handleRecordUpdate($record, array $data): Zone
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->zoneTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                ['name' => $fields['name'] ?? '']
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translations = [];
        if (isset($this->record->zoneTranslations)) {
            foreach ($this->record->zoneTranslations as $translation) {
                $translations[$translation->language_id] = [
                    'name' => $translation->name,
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
