<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\CartItemResource\Pages;
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

class CartItemResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = CartItem::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 104;

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

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();

        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.cart_item.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('cart_id')
                            ->label(__('filament.cart_item.cart_id'))
                            ->relationship('cart', 'id')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->hiddenOn([CartItemsRelationManager::class])
                            ->helperText(__('filament.cart_item.cart_id_helper')),
                        Forms\Components\Select::make('product_id')
                            ->label(__('filament.cart_item.product'))
                            ->relationship('product', 'id', function ($query) {
                                return $query->with('productTranslations');
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
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
                            ->columnSpan(1)
                            ->reactive()
                            ->afterStateUpdated(function ($set) {
                                // 商品变更时清空规格和价格
                                $set('product_variant_id', null);
                                $set('price', null);
                            })
                            ->helperText(__('filament.cart_item.product_helper')),
                        Forms\Components\Select::make('product_variant_id')
                            ->label(__('filament.cart_item.variant'))
                            ->options(function ($get) use ($lang) {
                                $productId = $get('product_id');
                                if (! $productId) {
                                    return [];
                                }
                                $variants = \App\Models\ProductVariant::where('product_id', $productId)
                                    ->with('specificationValues.specificationValueTranslations')
                                    ->get();
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
                                    $label = implode(' / ', array_filter($specNames)) ?: ($variant->sku ?? $variant->id);
                                    // 确保ID是字符串类型，以便在Select中正确匹配
                                    $options[(string) $variant->id] = $label;
                                }

                                return $options;
                            })
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->columnSpan(1)
                            ->reactive()
                            ->afterStateUpdated(function ($get, $set) use ($service, $currentCurrencyCode) {
                                // 选择规格后自动填充价格
                                $variantId = $get('product_variant_id');
                                if ($variantId) {
                                    $variant = \App\Models\ProductVariant::find($variantId);
                                    if ($variant && $variant->price) {
                                        $set('price', $variant->price);
                                    }
                                }
                            })
                            ->helperText(__('filament.cart_item.variant_helper')),
                        Forms\Components\TextInput::make('price')
                            ->label(__('filament.cart_item.price'))
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix(function () use ($service, $currentCurrencyCode) {
                                $currency = $service->getCurrencyByCode($currentCurrencyCode);
                                return $currency ? $currency->symbol : '';
                            })
                            ->columnSpan(1)
                            ->helperText(__('filament.cart_item.price_helper')),
                        Forms\Components\TextInput::make('qty')
                            ->label(__('filament.cart_item.qty'))
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->columnSpan(1)
                            ->helperText(__('filament.cart_item.qty_helper')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                    ->with([
                        'cart.user',
                        'product.productTranslations',
                        'product.productVariants',
                        'productVariant.specificationValues.specificationValueTranslations',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('cart.id')
                    ->label(__('filament.cart_item.cart_id'))
                    ->numeric()
                    ->searchable()
                    ->sortable()
                    ->hiddenOn([CartItemsRelationManager::class])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cart.user.name')
                    ->label(__('filament.cart_item.user'))
                    ->searchable()
                    ->sortable()
                    ->hiddenOn([CartItemsRelationManager::class])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament.cart_item.product'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $product = $record->product;
                        if (! $product) {
                            return null;
                        }
                        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $product->productTranslations->first();
                        return $first ? $first->name : $product->id;
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('product.productTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('products', 'cart_items.product_id', '=', 'products.id')
                            ->leftJoin('product_translations', function ($join) use ($langId) {
                                $join->on('products.id', '=', 'product_translations.product_id')
                                    ->where('product_translations.language_id', '=', $langId);
                            })
                            ->orderBy('product_translations.name', $direction)
                            ->select('cart_items.*')
                            ->groupBy('cart_items.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('productVariant.specifications')
                    ->label(__('filament.cart_item.variant'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $variant = $record->productVariant;
                        if (! $variant) {
                            return __('filament.cart_item.no_variant');
                        }
                        $specNames = [];
                        foreach ($variant->specificationValues as $specValue) {
                            $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
                            $specNames[] = $translation && $translation->name
                                ? $translation->name
                                : ($specValue->specificationValueTranslations->first()->name ?? '');
                        }
                        return implode(' / ', array_filter($specNames)) ?: $variant->sku ?? $variant->id;
                    })
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament.cart_item.price'))
                    ->getStateUsing(function ($record) use ($service, $currentCurrencyCode) {
                        // 从 productVariant 获取价格
                        $price = null;
                        if ($record->productVariant && $record->productVariant->price) {
                            $price = $record->productVariant->price;
                        } elseif ($record->product && $record->product->relationLoaded('productVariants')) {
                            // 如果没有规格，尝试获取商品的第一个规格价格
                            $variant = $record->product->productVariants->first();
                            $price = $variant ? $variant->price : null;
                        }
                        
                        if (!$price || $price == 0) {
                            return '-';
                        }
                        return $service->convertWithSymbol($price, $currentCurrencyCode);
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction): \Illuminate\Database\Eloquent\Builder {
                        return $query->leftJoin('product_variants', 'cart_items.product_variant_id', '=', 'product_variants.id')
                            ->orderBy('product_variants.price', $direction)
                            ->select('cart_items.*')
                            ->groupBy('cart_items.id');
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label(__('filament.cart_item.qty'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('filament.cart_item.subtotal'))
                    ->getStateUsing(function ($record) use ($service, $currentCurrencyCode) {
                        // 从 productVariant 获取价格
                        $price = null;
                        if ($record->productVariant && $record->productVariant->price) {
                            $price = $record->productVariant->price;
                        } elseif ($record->product && $record->product->relationLoaded('productVariants')) {
                            $variant = $record->product->productVariants->first();
                            $price = $variant ? $variant->price : null;
                        }
                        
                        $subtotal = ($price ?? 0) * ($record->qty ?? 0);
                        if ($subtotal == 0) {
                            return '-';
                        }
                        return $service->convertWithSymbol($subtotal, $currentCurrencyCode);
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction): \Illuminate\Database\Eloquent\Builder {
                        return $query->leftJoin('product_variants', 'cart_items.product_variant_id', '=', 'product_variants.id')
                            ->orderByRaw("(product_variants.price * cart_items.qty) {$direction}")
                            ->select('cart_items.*')
                            ->groupBy('cart_items.id');
                    })
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cart_id')
                    ->label(__('filament.cart_item.cart_id'))
                    ->relationship('cart', 'id')
                    ->searchable()
                    ->preload()
                    ->hiddenOn([CartItemsRelationManager::class]),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('filament.cart_item.product'))
                    ->relationship('product', 'id', function ($query) {
                        return $query->with('productTranslations');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->productTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('created_at', 'desc'));
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
