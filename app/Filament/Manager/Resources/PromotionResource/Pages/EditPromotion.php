<?php

namespace App\Filament\Manager\Resources\PromotionResource\Pages;

use App\Filament\Manager\Resources\PromotionResource;
use App\Models\Promotion;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotion extends EditRecord
{
    protected static string $resource = PromotionResource::class;

    protected function handleRecordUpdate($record, array $data): Promotion
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->promotionTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                [
                    'name' => $fields['name'] ?? '',
                    'description' => $fields['description'] ?? '',
                ]
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translations = [];
        if (isset($this->record->promotionTranslations)) {
            foreach ($this->record->promotionTranslations as $translation) {
                $translations[$translation->language_id] = [
                    'name' => $translation->name,
                    'description' => $translation->description,
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
