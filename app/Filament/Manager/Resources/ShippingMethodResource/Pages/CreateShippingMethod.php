<?php

namespace App\Filament\Manager\Resources\ShippingMethodResource\Pages;

use App\Filament\Manager\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingMethod extends CreateRecord
{
    protected static string $resource = ShippingMethodResource::class;

    protected function handleRecordCreation(array $data): ShippingMethod
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $shippingMethod = static::getModel()::create($data);

        foreach ($translations as $languageId => $fields) {
            $shippingMethod->shippingMethodTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $shippingMethod;
    }
}
