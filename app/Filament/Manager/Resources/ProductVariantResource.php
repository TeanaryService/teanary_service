<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ProductVariantResource\Pages;
use App\Filament\Manager\Resources\ProductVariantResource\RelationManagers;
use App\Models\ProductVariant;
use App\Services\LocaleCurrencyService;
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

class ProductVariantResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = ProductVariant::class;

    public static function getLabel(): string
    {
        return __('filament.ProductVariantResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ProductVariantResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ProductVariantResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ProductVariantResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ProductVariantResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.ProductVariantResource.sort');
    }

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);

        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label(__('filament_product_variant.product_id'))
                    ->relationship('product', 'id')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->productTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('sku')
                    ->label(__('filament_product_variant.sku'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label(__('filament_product_variant.price'))
                    ->numeric()
                    ->default(null)
                    ->prefix('￥'),
                Forms\Components\TextInput::make('cost')
                    ->label(__('filament_product_variant.cost'))
                    ->numeric()
                    ->default(null)
                    ->prefix('￥'),
                Forms\Components\TextInput::make('stock')
                    ->label(__('filament_product_variant.stock'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('weight')
                    ->label(__('filament_product_variant.weight'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('length')
                    ->label(__('filament_product_variant.length'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('width')
                    ->label(__('filament_product_variant.width'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('height')
                    ->label(__('filament_product_variant.height'))
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament_product_variant.product_id'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $product = $record->product;
                        if (!$product) return null;
                        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $product->productTranslations->first();
                        return $first ? $first->name : $product->id;
                    }),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('filament_product_variant.sku'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament_product_variant.price'))
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label(__('filament_product_variant.cost'))
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label(__('filament_product_variant.stock'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label(__('filament_product_variant.weight'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('length')
                    ->label(__('filament_product_variant.length'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('width')
                    ->label(__('filament_product_variant.width'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('height')
                    ->label(__('filament_product_variant.height'))
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
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}
