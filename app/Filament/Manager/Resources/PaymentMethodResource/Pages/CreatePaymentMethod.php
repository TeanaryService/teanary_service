<?php

namespace App\Filament\Manager\Resources\PaymentMethodResource\Pages;

use App\Filament\Manager\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;

    protected function handleRecordCreation(array $data): PaymentMethod
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $paymentMethod = static::getModel()::create($data);

        foreach ($translations as $languageId => $fields) {
            $paymentMethod->paymentMethodTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $paymentMethod;
    }
}
