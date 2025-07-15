<?php

namespace App\Filament\Manager\Resources;

use App\Enums\ProductStatusEnum;
use App\Filament\Manager\Resources\ProductResource\Pages;
use App\Filament\Manager\Resources\ProductResource\RelationManagers;
use App\Models\Product;
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

class ProductResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $pluralLabel = '商品管理';
    protected static ?string $label = '商品管理';
    protected static ?int $navigationSort = 200;
    protected static ?string $navigationGroup = '商品管理';
    protected static ?string $navigationLabel = '商品管理';
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->label('商品标识')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('商品状态')
                    ->options(ProductStatusEnum::options())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label('商品标识')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn($state): string => $state->label())
                    ->label('商品状态'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
