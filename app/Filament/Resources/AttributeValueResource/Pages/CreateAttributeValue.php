<?php

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use App\Models\AttributeValue;
use Filament\Resources\Pages\CreateRecord;

class CreateAttributeValue extends CreateRecord
{
    protected static string $resource = AttributeValueResource::class;

    protected function handleRecordCreation(array $data): AttributeValue
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $attributeValue = AttributeValue::create($data);

        foreach ($translations as $languageId => $fields) {
            $attributeValue->attributeValueTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $attributeValue;
    }
}
