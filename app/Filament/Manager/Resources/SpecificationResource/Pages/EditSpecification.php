<?php

namespace App\Filament\Manager\Resources\SpecificationResource\Pages;

use App\Filament\Manager\Resources\SpecificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecification extends EditRecord
{
    protected static string $resource = SpecificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
