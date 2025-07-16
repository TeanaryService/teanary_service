<?php

namespace App\Filament\Manager\Resources\AddressResource\Pages;

use App\Filament\Manager\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;
}
