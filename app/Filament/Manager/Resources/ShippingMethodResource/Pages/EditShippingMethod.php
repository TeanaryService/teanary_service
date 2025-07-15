<?php

namespace App\Filament\Manager\Resources\ShippingMethodResource\Pages;

use App\Filament\Manager\Resources\ShippingMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShippingMethod extends EditRecord
{
    protected static string $resource = ShippingMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
