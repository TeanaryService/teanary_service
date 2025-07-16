<?php

namespace App\Filament\Manager\Resources\UserGroupResource\Pages;

use App\Filament\Manager\Resources\UserGroupResource;
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
}
