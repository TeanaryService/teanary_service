<?php

namespace App\Filament\Manager\Resources\ZoneResource\Pages;

use App\Filament\Manager\Resources\ZoneResource;
use App\Models\Zone;
use Filament\Resources\Pages\CreateRecord;

class CreateZone extends CreateRecord
{
    protected static string $resource = ZoneResource::class;

    protected function handleRecordCreation(array $data): Zone
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $zone = static::getModel()::create($data);

        foreach ($translations as $languageId => $fields) {
            $zone->zoneTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $zone;
    }
}
