<?php

namespace App\Filament\Manager\Resources\ShippingMethodResource\Pages;

use App\Filament\Manager\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShippingMethod extends EditRecord
{
    protected static string $resource = ShippingMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): ShippingMethod
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->shippingMethodTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                ['name' => $fields['name'] ?? '']
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translations = [];
        if (isset($this->record->shippingMethodTranslations)) {
            foreach ($this->record->shippingMethodTranslations as $translation) {
                $translations[$translation->language_id] = [
                    'name' => $translation->name,
                ];
            }
        }
        $data['translations'] = $translations;
        return $data;
    }
}
