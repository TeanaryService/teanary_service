<?php

namespace App\Filament\Manager\Resources\ProductVariantResource\Pages;

use App\Filament\Manager\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductVariant extends CreateRecord
{
    protected static string $resource = ProductVariantResource::class;
}
