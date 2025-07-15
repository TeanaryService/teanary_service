<?php

namespace App\Filament\Manager\Resources\OrderResource\Pages;

use App\Filament\Manager\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
