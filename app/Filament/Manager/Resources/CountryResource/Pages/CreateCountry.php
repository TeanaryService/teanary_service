<?php

namespace App\Filament\Manager\Resources\CountryResource\Pages;

use App\Filament\Manager\Resources\CountryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;
}
