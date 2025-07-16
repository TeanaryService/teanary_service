<?php

namespace App\Filament\Manager\Resources\UserGroupResource\Pages;

use App\Filament\Manager\Resources\UserGroupResource;
use App\Models\UserGroup;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserGroup extends EditRecord
{
    protected static string $resource = UserGroupResource::class;

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
        if (isset($this->record->userGroupTranslations)) {
            foreach ($this->record->userGroupTranslations as $translation) {
                $translations[$translation->language_id]['name'] = $translation->name;
            }
        }
        $data['translations'] = $translations;
        return $data;
    }

    protected function handleRecordUpdate($record, array $data): UserGroup
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        foreach ($translations as $languageId => $fields) {
            $record->userGroupTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                ['name' => $fields['name'] ?? '']
            );
        }

        return $record;
    }
}
