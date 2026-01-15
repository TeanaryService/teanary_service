<?php

namespace App\Filament\Manager\Resources\SpecificationResource\Pages;

use App\Filament\Manager\Resources\SpecificationResource;
use App\Models\Specification;
use Filament\Resources\Pages\CreateRecord;

class CreateSpecification extends CreateRecord
{
    protected static string $resource = SpecificationResource::class;

    protected function handleRecordCreation(array $data): Specification
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $spec = static::getModel()::create($data);

        foreach ($translations as $languageId => $fields) {
            $spec->specificationTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $spec;
    }
}
