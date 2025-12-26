<?php

namespace App\Filament\Resources\UserGroupResource\Pages;

use App\Filament\Resources\UserGroupResource;
use App\Models\UserGroup;
use Filament\Resources\Pages\CreateRecord;

class CreateUserGroup extends CreateRecord
{
    protected static string $resource = UserGroupResource::class;

    protected function handleRecordCreation(array $data): UserGroup
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $userGroup = UserGroup::create($data);

        foreach ($translations as $languageId => $fields) {
            $userGroup->userGroupTranslations()->create([
                'language_id' => $languageId,
                'name' => $fields['name'] ?? '',
            ]);
        }

        return $userGroup;
    }
}
