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

    protected static ?string $model = Product::class;

    public static function getLabel(): string
    {
        return __('filament.ProductResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ProductResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ProductResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ProductResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ProductResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.ProductResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->label(__('filament_product.slug'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label(__('filament_product.status'))
                    ->options(ProductStatusEnum::options())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament_product.slug'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn($state): string => $state->label())
                    ->label(__('filament_product.status')),
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
