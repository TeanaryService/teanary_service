<?php

namespace App\Filament\Manager\Resources\OrderItemResource\Pages;

use App\Filament\Manager\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderItems extends ListRecords
{
    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
