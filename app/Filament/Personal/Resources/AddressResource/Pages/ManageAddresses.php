<?php

namespace App\Filament\Personal\Resources\AddressResource\Pages;

use App\Filament\Personal\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAddresses extends ManageRecords
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
