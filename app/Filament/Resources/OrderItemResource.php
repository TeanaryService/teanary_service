<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderItemResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\OrderItem;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = OrderItem::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 101;

    public static function getLabel(): string
    {
        return __('filament.OrderItemResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.OrderItemResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.OrderItemResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.OrderItemResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.OrderItemResource.icon');
    }

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);
        $currency = $service->getCurrencyByCode(session('currency'));

        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label(__('filament.order_item.order_id'))
                    ->relationship('order', 'id')
                    ->hiddenOn([OrderItemsRelationManager::class])
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->label(__('filament.order_item.product_id'))
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
                        $set('product_variant_id', null);
                    }),
                Forms\Components\Select::make('product_variant_id')
                    ->label(__('filament.order_item.product_variant_id'))
                    ->options(function ($get) {
                        $productId = $get('product_id');
                        if (! $productId) {
                            return [];
                        }
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $variants = \App\Models\ProductVariant::where('product_id', $productId)->with('specificationValues.specificationValueTranslations')->get();
                        $options = [];
                        foreach ($variants as $variant) {
                            $specNames = [];
                            foreach ($variant->specificationValues as $specValue) {
                                $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                $specNames[] = $translation && $translation->name
                                    ? $translation->name
                                    : ($specValue->specificationValueTranslations->first()->name ?? '');
                            }
                            // 确保ID是字符串类型，以便在Select中正确匹配
                            $options[(string) $variant->id] = implode(' / ', array_filter($specNames)) ?: $variant->id;
                        }

                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->default(null)
                    ->reactive(),
                Forms\Components\TextInput::make('qty')
                    ->label(__('filament.order_item.qty'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->label(__('filament.order_item.price'))
                    ->required()
                    ->numeric()
                    ->prefix($currency->symbol),
            ]);
    }

    public static function table(Table $table): Table
    {
        $service = app(LocaleCurrencyService::class);

        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label(__('filament.order_item.order_id'))
                    ->numeric()
                    ->hiddenOn([OrderItemsRelationManager::class])
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament.order_item.product_id'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
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
                    }),
                Tables\Columns\TextColumn::make('productVariant.id')
                    ->label(__('filament.order_item.product_variant_id'))
                    ->getStateUsing(function ($record) {
                        $variant = $record->productVariant;
                        if (! $variant) {
                            return null;
                        }
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
                    ->label(__('filament.order_item.qty'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament.order_item.price'))
                    ->formatStateUsing(function ($record, $state) use ($service) {
                        return $service->convertWithSymbol($state, session('currency'), $record->order->currency->code);
                    }),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions(),
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
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
