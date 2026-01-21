@php
    $breadcrumbs = buildUserCenterBreadcrumbs('orders', __('orders.order_details'), __('orders.my_orders'), locaRoute('auth.orders'));
    $localeService = app(\App\Services\LocaleCurrencyService::class);
    $lang = $localeService->getLanguageByCode(session('lang'));
    $orderCurrency = $localeService->getCurrencies()->find($order->currency_id);
    $statusConfig = [
        'pending' => ['color' => 'bg-yellow-100 text-yellow-800 border-yellow-200', 'icon' => 'heroicon-o-clock'],
        'paid' => ['color' => 'bg-blue-100 text-blue-800 border-blue-200', 'icon' => 'heroicon-o-check-circle'],
        'shipped' => ['color' => 'bg-purple-100 text-purple-800 border-purple-200', 'icon' => 'heroicon-o-truck'],
        'completed' => ['color' => 'bg-green-100 text-green-800 border-green-200', 'icon' => 'heroicon-o-check-badge'],
        'cancelled' => ['color' => 'bg-red-100 text-red-800 border-red-200', 'icon' => 'heroicon-o-x-circle'],
        'after_sale' => ['color' => 'bg-orange-100 text-orange-800 border-orange-200', 'icon' => 'heroicon-o-exclamation-triangle'],
        'after_sale_done' => ['color' => 'bg-gray-100 text-gray-800 border-gray-200', 'icon' => 'heroicon-o-check'],
    ];
    $statusInfo = $statusConfig[$order->status->value] ?? $statusConfig['pending'];
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-users.sidebar active="orders" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('orders.order_details') }}</h1>
                </div>

        @if (session()->has('message'))
            <div class="mb-4 rounded-md bg-teal-50 p-4">
                <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <div class="space-y-6">
            <!-- 订单状态头部 -->
            <div class="bg-gradient-to-r from-teal-50 via-blue-50 to-teal-50 rounded-xl shadow-sm border border-teal-200 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white rounded-xl shadow-sm flex items-center justify-center">
                            <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h2 class="text-xl font-bold text-gray-900">{{ __('orders.order_details') }}</h2>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-semibold border {{ $statusInfo['color'] }}">
                                    <x-dynamic-component :component="$statusInfo['icon']" class="w-4 h-4" />
                                    {{ $order->status->label() }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                    <strong>{{ __('orders.order_no') }}:</strong> {{ $order->order_no }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $order->created_at->format('Y-m-d H:i:s') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600 mb-1">{{ __('orders.total') }}</div>
                        <div class="text-3xl font-bold text-teal-600">
                            {{ $localeService->formatWithSymbol($order->total, $orderCurrency->code) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 左侧：商品信息 -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- 商品列表 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                {{ __('orders.product_info') }}
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach($order->orderItems as $item)
                                @php
                                    $image = $item->productVariant
                                        ? $item->productVariant->getFirstMediaUrl('image', 'thumb')
                                        : ($item->product->getFirstMediaUrl('images', 'thumb') ?: asset('logo.svg'));
                                    $specs = $item->productVariant
                                        ? $item->productVariant->specificationValues
                                            ->map(function ($sv) use ($lang) {
                                                $trans = $sv->specificationValueTranslations
                                                    ->where('language_id', $lang?->id)
                                                    ->first();
                                                return $trans && $trans->name ? $trans->name : $sv->id;
                                            })
                                            ->implode(' / ')
                                        : '';
                                    $productName = $item->product->productTranslations
                                        ->where('language_id', $lang?->id)
                                        ->first()?->name ?? $item->product->slug;
                                @endphp

                                <div class="p-6 hover:bg-gray-50 transition-colors">
                                    <div class="flex gap-4">
                                        <div class="flex-shrink-0">
                                            <img src="{{ $image }}" alt="{{ $productName }}" 
                                                 class="w-24 h-24 rounded-lg object-cover border border-gray-200 bg-gray-50">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-base font-semibold text-gray-900 mb-1">
                                                {{ $productName }}
                                            </h4>
                                            @if($specs)
                                                <p class="text-sm text-gray-500 mb-3">{{ $specs }}</p>
                                            @endif
                                            <div class="flex items-center gap-6 text-sm text-gray-600">
                                                <span>{{ __('orders.quantity') }}: <strong class="text-gray-900">{{ $item->qty }}</strong></span>
                                                <span>{{ __('orders.unit_price') }}: <strong class="text-gray-900">{{ $localeService->formatWithSymbol($item->price, $orderCurrency->code) }}</strong></span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 text-right">
                                            <div class="text-lg font-bold text-gray-900">
                                                {{ $localeService->formatWithSymbol($item->price * $item->qty, $orderCurrency->code) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 配送信息 -->
                    @if($order->orderShipments->isNotEmpty())
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    {{ __('orders.shipments') }}
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                @foreach($order->orderShipments as $orderShipment)
                                    <div class="border-l-4 border-teal-500 pl-4 py-2">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <span class="font-semibold text-gray-900">{{ $orderShipment->shipping_method->label() }}</span>
                                        </div>
                                        @if($orderShipment->tracking_number)
                                            <div class="text-sm text-gray-600 mb-1">
                                                <strong>{{ __('orders.tracking_number') }}:</strong> 
                                                <span class="font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $orderShipment->tracking_number }}</span>
                                            </div>
                                        @endif
                                        @if($orderShipment->notes)
                                            <div class="text-sm text-gray-600">
                                                <strong>{{ __('orders.shipping_notes') }}:</strong> {{ $orderShipment->notes }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 右侧：订单信息 -->
                <div class="space-y-6">
                    <!-- 收货地址 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ __('orders.shipping_address') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2 text-sm text-gray-700">
                                <p class="font-semibold text-gray-900 text-base">
                                    {{ $order->shippingAddress->lastname }}{{ $order->shippingAddress->firstname }}
                                </p>
                                <p class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {{ $order->shippingAddress->telephone }}
                                </p>
                                <p>{{ $order->shippingAddress->address_1 }}</p>
                                @if($order->shippingAddress->address_2)
                                    <p>{{ $order->shippingAddress->address_2 }}</p>
                                @endif
                                <p>
                                    {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->postcode }}
                                </p>
                                <p>
                                    {{ $order->shippingAddress->zone?->zoneTranslations->where('language_id', $lang->id)->first()?->name ?? $order->shippingAddress->zone?->name ?? '' }}, 
                                    {{ $order->shippingAddress->country->countryTranslations->where('language_id', $lang->id)->first()?->name ?? $order->shippingAddress->country->name ?? '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 价格明细 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-5m-6 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('orders.price_summary') }}
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ __('orders.subtotal') }}</span>
                                <span class="font-medium">{{ $localeService->formatWithSymbol($order->total - ($order->shipping_fee ?? 0), $orderCurrency->code) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ __('orders.shipping_fee') }}</span>
                                <span class="font-medium">{{ $localeService->formatWithSymbol($order->shipping_fee ?? 0, $orderCurrency->code) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-semibold text-gray-900">{{ __('orders.total') }}</span>
                                    <span class="text-xl font-bold text-teal-600">
                                        {{ $localeService->formatWithSymbol($order->total, $orderCurrency->code) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="space-y-3">
                            @if($order->status->canBePaid())
                                <button wire:click="payOrder" 
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    {{ __('orders.pay_now') }}
                                </button>
                            @endif

                            @if($order->status->canBeCancelled())
                                <button wire:click="cancelOrder" 
                                        wire:confirm="{{ __('orders.confirm_cancel') }}"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    {{ __('orders.cancel_order') }}
                                </button>
                            @endif

                            <a href="{{ locaRoute('auth.orders') }}" 
                               class="block w-full text-center px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                {{ __('app.back_to_orders') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('orders.order_details') }}" description="{{ __('orders.order_details') }}"
        keywords="{{ __('orders.order_details') }}" />
@endPushOnce
