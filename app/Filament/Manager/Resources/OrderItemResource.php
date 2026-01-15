<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\OrderItemResource\Pages;
use App\Filament\Manager\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
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
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                    ->with([
                        'order.currency',
                        'product.productTranslations',
                        'productVariant.specificationValues.specificationValueTranslations',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('order.order_no')
                    ->label(__('filament.order_item.order_no'))
                    ->searchable()
                    ->sortable()
                    ->hiddenOn([OrderItemsRelationManager::class])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament.order_item.product'))
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
                    ->wrap(),
                Tables\Columns\TextColumn::make('productVariant.specifications')
                    ->label(__('filament.order_item.variant'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $variant = $record->productVariant;
                        if (! $variant) {
                            return __('filament.order_item.no_variant');
                        }
                        $specNames = [];
                        foreach ($variant->specificationValues as $specValue) {
                            $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
                            $specNames[] = $translation && $translation->name
                                ? $translation->name
                                : ($specValue->specificationValueTranslations->first()->name ?? '');
                        }
                        return implode(' / ', array_filter($specNames)) ?: ($variant->sku ?? $variant->id);
                    })
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament.order_item.price'))
                    ->getStateUsing(function ($record) use ($service, $currentCurrencyCode) {
                        if (!$record->price || $record->price == 0) {
                            return '-';
                        }
                        $orderCurrencyCode = $record->order->currency?->code ?? $service->getDefaultCurrencyCode();
                        return $service->convertWithSymbol($record->price, $currentCurrencyCode, $orderCurrencyCode);
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label(__('filament.order_item.qty'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('filament.order_item.subtotal'))
                    ->getStateUsing(function ($record) use ($service, $currentCurrencyCode) {
                        $subtotal = ($record->price ?? 0) * ($record->qty ?? 0);
                        if ($subtotal == 0) {
                            return '-';
                        }
                        $orderCurrencyCode = $record->order->currency?->code ?? $service->getDefaultCurrencyCode();
                        return $service->convertWithSymbol($subtotal, $currentCurrencyCode, $orderCurrencyCode);
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction): \Illuminate\Database\Eloquent\Builder {
                        return $query->orderByRaw("(price * qty) {$direction}");
                    })
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_id')
                    ->label(__('filament.order_item.order_id'))
                    ->relationship('order', 'order_no')
                    ->searchable()
                    ->preload()
                    ->hiddenOn([OrderItemsRelationManager::class]),
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
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
