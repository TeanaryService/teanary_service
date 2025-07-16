<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ShippingMethodResource\Pages;
use App\Filament\Manager\Resources\ShippingMethodResource\RelationManagers;
use App\Models\ShippingMethod;
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

class ShippingMethodResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = ShippingMethod::class;

    public static function getLabel(): string
    {
        return __('filament.ShippingMethodResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ShippingMethodResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ShippingMethodResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ShippingMethodResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ShippingMethodResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.ShippingMethodResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('filament_shipping_method.code'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament_shipping_method.active'))
                    ->required(),
                Forms\Components\TextInput::make('api_url')
                    ->label(__('filament_shipping_method.api_url'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament_shipping_method.code'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament_shipping_method.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('api_url')
                    ->label(__('filament_shipping_method.api_url'))
                    ->searchable(),
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
            'index' => Pages\ListShippingMethods::route('/'),
            'create' => Pages\CreateShippingMethod::route('/create'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
        ];
    }
}
