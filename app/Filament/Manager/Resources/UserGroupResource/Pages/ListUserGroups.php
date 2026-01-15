<?php

namespace App\Filament\Manager\Resources\UserGroupResource\Pages;

use App\Filament\Manager\Resources\UserGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserGroups extends ListRecords
{
    protected static string $resource = UserGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
