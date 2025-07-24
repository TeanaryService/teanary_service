<?php

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCartItem extends EditRecord
{
    protected static string $resource = CartItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
