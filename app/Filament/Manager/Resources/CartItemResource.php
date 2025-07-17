<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\CartItemResource\Pages;
use App\Filament\Manager\Resources\CartItemResource\RelationManagers;
use App\Filament\Manager\Resources\CartResource\RelationManagers\CartItemsRelationManager;
use App\Models\CartItem;
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

class CartItemResource extends Resource
{

    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = CartItem::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): string
    {
        return __('filament.CartItemResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.CartItemResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.CartItemResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.CartItemResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.CartItemResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.CartItemResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cart_id')
                    ->label(__('filament_cart_item.cart_id'))
                    ->relationship('cart', 'id')
                    ->hiddenOn([CartItemsRelationManager::class])
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->label(__('filament_cart_item.product_id'))
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
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($set) {
                        // 商品变更时清空规格
                        $set('product_variant_id', null);
                    }),
                Forms\Components\Select::make('product_variant_id')
                    ->label(__('filament_cart_item.product_variant_id'))
                    ->options(function ($get) {
                        $productId = $get('product_id');
                        if (!$productId) {
                            return [];
                        }
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $variants = \App\Models\ProductVariant::where('product_id', $productId)->with('specificationValues.specificationValueTranslations')->get();
                        $options = [];
                        foreach ($variants as $variant) {
                            // 拼接规格值多语言名
                            $specNames = [];
                            foreach ($variant->specificationValues as $specValue) {
                                $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                $specNames[] = $translation && $translation->name
                                    ? $translation->name
                                    : ($specValue->specificationValueTranslations->first()->name ?? '');
                            }
                            $options[$variant->id] = implode(' / ', array_filter($specNames)) ?: $variant->id;
                        }
                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->default(null)
                    ->reactive(),
                Forms\Components\TextInput::make('qty')
                    ->label(__('filament_cart_item.qty'))
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('price')
                    ->label(__('filament_cart_item.price'))
                    ->required()
                    ->numeric()
                    ->prefix('￥'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('cart.id')
                    ->label(__('filament_cart_item.cart_id'))
                    ->numeric()
                    ->hiddenOn([CartItemsRelationManager::class])
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament_cart_item.product_id'))
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
                Tables\Columns\TextColumn::make('productVariant.id')
                    ->label(__('filament_cart_item.product_variant_id'))
                    ->getStateUsing(function ($record) {
                        $variant = $record->productVariant;
                        if (!$variant) return null;
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $specNames = [];
                        foreach ($variant->specificationValues as $specValue) {
                            $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
                            $specNames[] = $translation && $translation->name
                                ? $translation->name
                                : ($specValue->specificationValueTranslations->first()->name ?? '');
                        }
                        return implode(' / ', array_filter($specNames)) ?: $variant->id;
                    }),
                Tables\Columns\TextColumn::make('qty')
                    ->label(__('filament_cart_item.qty'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament_cart_item.price'))
                    ->money('CNY')
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
            'index' => Pages\ListCartItems::route('/'),
            'create' => Pages\CreateCartItem::route('/create'),
            'edit' => Pages\EditCartItem::route('/{record}/edit'),
        ];
    }
}
