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

    protected static ?string $pluralLabel = '币种管理';
    protected static ?string $label = '币种管理';
    protected static ?int $navigationSort = 401;
    protected static ?string $navigationGroup = '系统设置';
    protected static ?string $navigationLabel = '币种管理';
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('币种代码')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('name')
                    ->label('币种名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('symbol')
                    ->label('币种符号')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('exchange_rate')
                    ->label('汇率')
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
                    ->label('币种代码')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('币种名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('symbol')
                    ->label('币种符号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->label('汇率')
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
