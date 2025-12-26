<?php

namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use App\Models\Country;
use Filament\Resources\Pages\CreateRecord;

class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;

    protected function handleRecordCreation(array $data): Country
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $country = static::getModel()::create($data);

        foreach ($translations as $languageId => $fields) {
            $country->countryTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $country;
    }
}
