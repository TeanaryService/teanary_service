<?php

namespace App\Filament\Manager\Resources\CategoryResource\Pages;

use App\Filament\Manager\Resources\CategoryResource;
use App\Models\Category;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function handleRecordCreation(array $data): Category
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $category = Category::create($data);

        foreach ($translations as $languageId => $fields) {
            $category->categoryTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $category;
    }
}
