<?php

namespace App\Filament\Manager\Resources\SpecificationValueResource\Pages;

use App\Filament\Manager\Resources\SpecificationValueResource;
use App\Models\SpecificationValue;
use Filament\Resources\Pages\CreateRecord;

class CreateSpecificationValue extends CreateRecord
{
    protected static string $resource = SpecificationValueResource::class;

    protected function handleRecordCreation(array $data): SpecificationValue
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $specValue = static::getModel()::create($data);

        foreach ($translations as $languageId => $fields) {
            $specValue->specificationValueTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $specValue;
    }
}
