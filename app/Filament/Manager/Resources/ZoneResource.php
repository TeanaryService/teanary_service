<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ZoneResource\Pages;
use App\Filament\Manager\Resources\ZoneResource\RelationManagers;
use App\Models\Zone;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ZoneResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Zone::class;

    public static function getLabel(): string
    {
        return __('filament.ZoneResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ZoneResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ZoneResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ZoneResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ZoneResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.ZoneResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('country_id')
                    ->label(__('filament_zone.country_id'))
                    ->relationship('country', 'iso_code_2')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->label(__('filament_zone.code'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament_zone.active'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('country.iso_code_2')
                    ->label(__('filament_zone.country_id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament_zone.code'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament_zone.active'))
                    ->boolean(),
                ...static::getTimestampsColumns()
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions()
                ]),
            ]));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
