@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('orders', __('manager.orders.label'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[80vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="orders" />
            
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('manager.orders.label') }}</h1>
                    <x-widgets.button 
                        href="{{ locaRoute('manager.orders.create') }}" wire:navigate
                        class="inline-flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('app.create') }} 订单
                    </x-widgets.button>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-100 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                {{-- 筛选器 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input 
                                type="text" 
                                wire="live.debounce.300ms=search"
                                placeholder="订单号、用户名称、邮箱"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.status') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterStatus" 
                                :options="$statusOptions"
                                :multiple="false"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.currency') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterCurrencyId" 
                                :options="[['value' => '', 'label' => __('app.all')], ...collect($currencies)->map(fn($currency) => ['value' => $currency->id, 'label' => $currency->name . ' (' . $currency->symbol . ')'])->toArray()]"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.created_from') }}</x-widgets.label>
                            <x-widgets.input 
                                type="date" 
                                wire="live=createdFrom"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.created_until') }}</x-widgets.label>
                            <x-widgets.input 
                                type="date" 
                                wire="live=createdUntil"
                            />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-widgets.button 
                            wire:click="resetFilters"
                            variant="secondary"
                        >
                            {{ __('app.reset') }}
                        </x-widgets.button>
                    </div>
                </div>

                {{-- 订单列表 --}}
                @php
                    $localeService = app(\App\Services\LocaleCurrencyService::class);
                    $lang = $localeService->getLanguageByCode(session('lang'));
                    $statusConfig = [
                        'pending' => ['color' => 'bg-yellow-100 text-yellow-800 border-yellow-200', 'icon' => 'heroicon-o-clock'],
                        'paid' => ['color' => 'bg-blue-100 text-blue-800 border-blue-200', 'icon' => 'heroicon-o-check-circle'],
                        'shipped' => ['color' => 'bg-purple-100 text-purple-800 border-purple-200', 'icon' => 'heroicon-o-truck'],
                        'completed' => ['color' => 'bg-green-100 text-green-800 border-green-200', 'icon' => 'heroicon-o-check-badge'],
                        'cancelled' => ['color' => 'bg-red-100 text-red-800 border-red-200', 'icon' => 'heroicon-o-x-circle'],
                        'after_sale' => ['color' => 'bg-orange-100 text-orange-800 border-orange-200', 'icon' => 'heroicon-o-exclamation-triangle'],
                        'after_sale_done' => ['color' => 'bg-gray-100 text-gray-800 border-gray-200', 'icon' => 'heroicon-o-check'],
                    ];
                @endphp

                <div class="space-y-6">
                    @forelse($orders as $order)
                        @php
                            $orderCurrency = $localeService->getCurrencies()->find($order->currency_id);
                            $statusInfo = $statusConfig[$order->status->value] ?? $statusConfig['pending'];
                        @endphp

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                            <!-- 订单头部 -->
                            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-3 mb-1">
                                                <span class="text-sm font-medium text-gray-500">{{ __('manager.order.order_no') }}:</span>
                                                <span class="text-base font-bold text-gray-900">{{ $order->order_no }}</span>
                                            </div>
                                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                                @if($order->user)
                                                    <span class="flex items-center gap-1.5">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        {{ $order->user->name }} ({{ $order->user->email }})
                                                    </span>
                                                @endif
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $order->created_at->format('Y-m-d H:i:s') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $statusInfo['color'] }}">
                                            <x-dynamic-component :component="$statusInfo['icon']" class="w-3.5 h-3.5" />
                                            {{ $order->status->label() }}
                                        </span>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500 mb-0.5">{{ __('manager.order.total') }}</div>
                                            <div class="text-xl font-bold text-teal-600">
                                                {{ $localeService->formatWithSymbol($order->total, $orderCurrency->code) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 商品列表 -->
                            <div class="px-6 py-5">
                                <div class="space-y-4">
                                    @foreach($order->orderItems->take(3) as $index => $item)
                                        @php
                                            // 安全获取图片
                                            $image = asset('logo.svg');
                                            if ($item->productVariant) {
                                                $image = $item->productVariant->getFirstMediaUrl('image', 'thumb') ?: $image;
                                            } elseif ($item->product) {
                                                $image = $item->product->getFirstMediaUrl('images', 'thumb') ?: $image;
                                            }
                                            
                                            // 安全获取规格
                                            $specs = '';
                                            if ($item->productVariant && $item->productVariant->specificationValues) {
                                                $specs = $item->productVariant->specificationValues
                                                    ->map(function ($sv) use ($lang) {
                                                        $trans = $sv->specificationValueTranslations
                                                            ->where('language_id', $lang?->id)
                                                            ->first();
                                                        return $trans && $trans->name ? $trans->name : $sv->id;
                                                    })
                                                    ->implode(' / ');
                                            }
                                            
                                            // 安全获取产品名称
                                            $productName = '-';
                                            if ($item->product) {
                                                $translation = $item->product->productTranslations
                                                    ->where('language_id', $lang?->id)
                                                    ->first();
                                                $productName = $translation?->name ?? $item->product->slug ?? '-';
                                            }
                                        @endphp

                                        <div class="flex gap-4 pb-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                            <div class="flex-shrink-0">
                                                <img src="{{ $image }}" alt="{{ $productName }}" 
                                                     class="w-20 h-20 rounded-lg object-cover border border-gray-200 bg-gray-50">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-gray-900 line-clamp-2 mb-1">
                                                    {{ $productName }}
                                                </h4>
                                                @if($specs)
                                                    <p class="text-xs text-gray-500 mb-2">{{ $specs }}</p>
                                                @endif
                                                <div class="flex items-center gap-4 text-xs text-gray-600">
                                                    <span>{{ __('orders.quantity') }}: <strong class="text-gray-900">{{ $item->qty }}</strong></span>
                                                    <span>{{ __('orders.unit_price') }}: <strong class="text-gray-900">{{ $localeService->formatWithSymbol($item->price, $orderCurrency->code) }}</strong></span>
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 text-right">
                                                <div class="text-sm font-bold text-gray-900">
                                                    {{ $localeService->formatWithSymbol($item->price * $item->qty, $orderCurrency->code) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if($order->orderItems->count() > 3)
                                        <div class="pt-2 text-center">
                                            <span class="text-sm text-gray-500">
                                                {{ __('orders.and_more_items', ['count' => $order->orderItems->count() - 3]) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- 操作按钮 -->
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-sm text-gray-600">
                                        <span>{{ __('orders.total_items') }}: <strong class="text-gray-900">{{ $order->orderItems->sum('qty') }}</strong></span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-widgets.button 
                                            href="{{ locaRoute('manager.orders.edit', ['id' => $order->id]) }}" 
                                            wire:navigate
                                            class="inline-flex items-center gap-2"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            {{ __('app.edit') }}
                                        </x-widgets.button>
                                        <x-widgets.button 
                                            href="{{ locaRoute('manager.orders.detail', ['id' => $order->id]) }}" 
                                            wire:navigate
                                            variant="secondary"
                                            class="inline-flex items-center gap-2"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            {{ __('app.view') }}
                                        </x-widgets.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-16 text-center">
                            <div class="max-w-md mx-auto">
                                <svg class="mx-auto h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <h3 class="mt-6 text-xl font-semibold text-gray-900">{{ __('app.no_data') }}</h3>
                            </div>
                        </div>
                    @endforelse

                    {{-- 分页 --}}
                    @if($orders->hasPages())
                        <div class="mt-6">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('manager.orders.label') }}" />
