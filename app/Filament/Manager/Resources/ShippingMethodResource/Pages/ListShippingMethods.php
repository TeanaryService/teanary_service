<?php

namespace App\Filament\Manager\Resources\ShippingMethodResource\Pages;

use App\Filament\Manager\Resources\ShippingMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShippingMethods extends ListRecords
{
    protected static string $resource = ShippingMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
