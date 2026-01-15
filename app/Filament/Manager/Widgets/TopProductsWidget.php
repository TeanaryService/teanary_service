<?php

namespace App\Filament\Manager\Widgets;

use App\Models\OrderItem;
use App\Services\LocaleCurrencyService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 70;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return OrderItem::query()
            ->selectRaw('MIN(order_items.id) as id, order_items.product_id, SUM(order_items.qty) as total_qty')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('currencies', 'orders.currency_id', '=', 'currencies.id')
            ->groupBy('order_items.product_id')
            ->with(['product.productTranslations'])
            ->orderByDesc('total_qty')
            ->limit(10);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('filament.dashboard.widgets.top_products'))
            ->description(__('filament.dashboard.widgets.top_products_desc'))
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament.dashboard.widgets.product_name'))
                    ->getStateUsing(function ($record) {
                        if (!$record->product) {
                            return '';
                        }
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->product->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->product->productTranslations->first();

                        return $first ? $first->name : $record->product->slug;
                    })
                    ->searchable(false)
                    ->sortable(false),
                Tables\Columns\TextColumn::make('total_qty')
                    ->label(__('filament.dashboard.widgets.total_quantity'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0)),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label(__('filament.dashboard.widgets.total_revenue'))
                    ->getStateUsing(function ($record) {
                        $service = app(LocaleCurrencyService::class);
                        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();
                        
                        // 重新计算该产品的总销售额，考虑货币转换
                        $defaultCurrencyCode = $service->getDefaultCurrencyCode();
                        $totalRevenue = OrderItem::query()
                            ->with(['order.currency'])
                            ->where('product_id', $record->product_id)
                            ->get()
                            ->sum(function ($item) use ($service, $currentCurrencyCode, $defaultCurrencyCode) {
                                if (!$item->order || !$item->price || !$item->qty) {
                                    return 0;
                                }
                                // 如果订单没有货币，使用默认货币
                                $orderCurrencyCode = $item->order->currency?->code ?? $defaultCurrencyCode;
                                $itemTotal = $item->price * $item->qty;
                                // convert(amount, toCode, fromCode)
                                return $service->convert($itemTotal, $currentCurrencyCode, $orderCurrencyCode);
                            });
                        
                        return $service->convertWithSymbol($totalRevenue, $currentCurrencyCode);
                    })
                    ->sortable(false),
            ])
            ->defaultSort('total_qty', 'desc')
            ->paginated(false);
    }
}
