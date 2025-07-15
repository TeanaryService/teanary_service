<?php

namespace App\Filament\Manager\Resources\AttributeValueResource\Pages;

use App\Filament\Manager\Resources\AttributeValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttributeValue extends EditRecord
{
    protected static string $resource = AttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
