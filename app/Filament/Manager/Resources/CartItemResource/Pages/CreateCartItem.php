<?php

namespace App\Filament\Manager\Resources\CartItemResource\Pages;

use App\Filament\Manager\Resources\CartItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;
}
