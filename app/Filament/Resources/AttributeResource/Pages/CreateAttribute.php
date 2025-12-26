<?php

namespace App\Filament\Resources\AttributeResource\Pages;

use App\Filament\Resources\AttributeResource;
use App\Models\Attribute;
use Filament\Resources\Pages\CreateRecord;

class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;

    protected function handleRecordCreation(array $data): Attribute
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $attribute = Attribute::create($data);

        foreach ($translations as $languageId => $fields) {
            $attribute->attributeTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $attribute;
    }
}
