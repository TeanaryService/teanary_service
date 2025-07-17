<?php

namespace App\Filament\Manager\Resources\OrderItemResource\Pages;

use App\Filament\Manager\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderItem extends CreateRecord
{
    protected static string $resource = OrderItemResource::class;
}
