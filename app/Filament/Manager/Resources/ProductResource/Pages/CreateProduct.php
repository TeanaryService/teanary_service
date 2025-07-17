<?php

namespace App\Filament\Manager\Resources\ProductResource\Pages;

use App\Filament\Manager\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Product
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $product = static::getModel()::create($data);

        foreach ($translations as $languageId => $fields) {
            $product->productTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
                'description' => $fields['description'] ?? '',
                'short_description' => $fields['short_description'] ?? '',
            ]);
        }

        return $product;
    }
}
