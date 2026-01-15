<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                Forms\Components\Section::make(__('filament.currency.basic_info'))
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label(__('filament.currency.code'))
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1)
                            ->helperText(__('filament.currency.code_helper'))
                            ->dehydrateStateUsing(fn ($state) => $state ? strtoupper($state) : $state),
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.currency.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('symbol')
                            ->label(__('filament.currency.symbol'))
                            ->required()
                            ->maxLength(10)
                            ->columnSpan(1)
                            ->helperText(__('filament.currency.symbol_helper')),
                        Forms\Components\TextInput::make('exchange_rate')
                            ->label(__('filament.currency.exchange_rate'))
                            ->required()
                            ->numeric()
                            ->step(0.0001)
                            ->default(1.0000)
                            ->columnSpan(1)
                            ->helperText(__('filament.currency.exchange_rate_helper')),
                        Forms\Components\Toggle::make('default')
                            ->label(__('filament.language.is_default'))
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1)
                            ->helperText(__('filament.language.is_default_helper')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament.currency.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.currency.name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('symbol')
                    ->label(__('filament.currency.symbol'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->label(__('filament.currency.exchange_rate'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => number_format($state, 4)),
                Tables\Columns\IconColumn::make('default')
                    ->label(__('filament.language.is_default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('default')
                    ->label(__('filament.language.is_default'))
                    ->options([
                        1 => __('filament.language.default'),
                        0 => __('filament.language.not_default'),
                    ]),
                Tables\Filters\Filter::make('exchange_rate')
                    ->form([
                        Forms\Components\TextInput::make('exchange_rate_from')
                            ->label(__('filament.currency.exchange_rate_from'))
                            ->numeric(),
                        Forms\Components\TextInput::make('exchange_rate_until')
                            ->label(__('filament.currency.exchange_rate_until'))
                            ->numeric(),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['exchange_rate_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $rate): \Illuminate\Database\Eloquent\Builder => $query->where('exchange_rate', '>=', $rate),
                            )
                            ->when(
                                $data['exchange_rate_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $rate): \Illuminate\Database\Eloquent\Builder => $query->where('exchange_rate', '<=', $rate),
                            );
                    }),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
                ]),
            ])
            ->defaultSort('code'));
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
