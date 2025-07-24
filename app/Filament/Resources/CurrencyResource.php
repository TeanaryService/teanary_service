<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Filament\Resources\CurrencyResource\RelationManagers;
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
                    ->label(__('filament.currency.code'))
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.currency.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('symbol')
                    ->label(__('filament.currency.symbol'))
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('exchange_rate')
                    ->label(__('filament.currency.exchange_rate'))
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
                    ->label(__('filament.currency.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.currency.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('symbol')
                    ->label(__('filament.currency.symbol'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->label(__('filament.currency.exchange_rate'))
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
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
