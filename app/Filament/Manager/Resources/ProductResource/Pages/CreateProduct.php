<?php

namespace App\Filament\Manager\Resources\ProductResource\Pages;

use App\Filament\Manager\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
