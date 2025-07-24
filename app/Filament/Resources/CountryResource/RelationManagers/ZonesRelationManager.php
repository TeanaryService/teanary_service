<?php

namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Filament\Resources\ZoneResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ZonesRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament.country.zones');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.country.zones');
    }

    protected static string $relationship = 'zones';

    public function form(Form $form): Form
    {
        return ZoneResource::form($form);
    }

    public function table(Table $table): Table
    {
        return ZoneResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.country.zones')),
            ]);
    }
}
