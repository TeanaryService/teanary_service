<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Filament\Resources\PromotionResource;
use App\Models\Promotion;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePromotion extends CreateRecord
{
    protected static string $resource = PromotionResource::class;

    protected function handleRecordCreation(array $data): Promotion
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $promotion = static::getModel()::create($data);

        foreach ($translations as $languageId => $fields) {
            $promotion->promotionTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
                'description' => $fields['description'] ?? '',
            ]);
        }

        return $promotion;
    }
}
