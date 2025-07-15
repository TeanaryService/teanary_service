<?php

namespace App\Traits;

use Filament\Tables;

trait HasActions
{
    public static function getActions(): array
    {
        return [
            Tables\Actions\DeleteAction::make()
                ->requiresConfirmation(),
            Tables\Actions\EditAction::make(),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make()
                ->requiresConfirmation(),
        ];
    }
}
