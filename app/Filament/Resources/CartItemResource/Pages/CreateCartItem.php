<?php

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;
}
