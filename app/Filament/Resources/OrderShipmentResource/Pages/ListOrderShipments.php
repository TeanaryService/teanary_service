<?php

namespace App\Filament\Resources\OrderShipmentResource\Pages;

use App\Filament\Resources\OrderShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderShipments extends ListRecords
{
    protected static string $resource = OrderShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
