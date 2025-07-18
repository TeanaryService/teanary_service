<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\CurrencyResource\Pages;
use App\Filament\Manager\Resources\CurrencyResource\RelationManagers;
use App\Models\Currency;
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

class CurrencyResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Currency::class;
    protected static ?int $navigationSort = 402;

    public static function getLabel(): string
    {
        return __('filament.CurrencyResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.CurrencyResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.CurrencyResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.CurrencyResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.CurrencyResource.icon');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('filament_currency.code'))
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('name')
                    ->label(__('filament_currency.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('symbol')
                    ->label(__('filament_currency.symbol'))
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('exchange_rate')
                    ->label(__('filament_currency.exchange_rate'))
                    ->required()
                    ->numeric()
                    ->default(1.0000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament_currency.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament_currency.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('symbol')
                    ->label(__('filament_currency.symbol'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->label(__('filament_currency.exchange_rate'))
                    ->numeric()
                    ->sortable(),
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
            'index' => getFilamentUrl(Pages\ListCurrencies::class, '/'),
            'create' => getFilamentUrl(Pages\CreateCurrency::class, '/create'),
            'edit' => getFilamentUrl(Pages\EditCurrency::class, '/{record}/edit'),
        ];
    }
}
