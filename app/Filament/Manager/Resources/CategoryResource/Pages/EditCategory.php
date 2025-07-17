<?php

namespace App\Filament\Manager\Resources\CategoryResource\Pages;

use App\Filament\Manager\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 转换 translations 为嵌套数组
        $translations = [];
        if (isset($this->record->categoryTranslations)) {
            foreach ($this->record->categoryTranslations as $translation) {
                $translations[$translation->language_id]['name'] = $translation->name;
            }
        }
        $data['translations'] = $translations;
        return $data;
    }

    protected function handleRecordUpdate($record, array $data): Category
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->categoryTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                ['name' => $fields['name'] ?? '']
            );
        }

        return $record;
    }
}
