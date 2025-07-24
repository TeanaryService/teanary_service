<?php

namespace App\Filament\Resources\UserGroupResource\Pages;

use App\Filament\Resources\UserGroupResource;
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
