<?php

namespace App\Filament\Resources\SpecificationValueResource\Pages;

use App\Filament\Resources\SpecificationValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecificationValues extends ListRecords
{
    protected static string $resource = SpecificationValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
