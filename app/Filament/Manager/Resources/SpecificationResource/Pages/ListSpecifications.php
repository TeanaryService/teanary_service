<?php

namespace App\Filament\Manager\Resources\SpecificationResource\Pages;

use App\Filament\Manager\Resources\SpecificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecifications extends ListRecords
{
    protected static string $resource = SpecificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
