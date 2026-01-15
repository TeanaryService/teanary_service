<?php

namespace App\Filament\Manager\Resources\ProductVariantResource\Pages;

use App\Filament\Manager\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductVariants extends ListRecords
{
    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
