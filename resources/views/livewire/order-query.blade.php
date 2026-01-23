<div class="min-h-[60vh] bg-gradient-to-br from-teal-50 via-blue-50 to-teal-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- 页面标题 -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('orders.query_title') }}</h1>
            <p class="text-gray-600">{{ __('orders.query_description') }}</p>
        </div>

        <!-- 步骤1: 输入订单号或邮箱 -->
        @if($step === 1)
            <div class="bg-white rounded-xl shadow-lg p-8">
                <form wire:submit.prevent="sendVerificationCode">
                    <x-widgets.form-field 
                        :label="__('orders.query_order_no_or_email')"
                        labelFor="orderNoOrEmail"
                        error="orderNoOrEmail"
                    >
                        <x-widgets.input 
                            type="text" 
                            id="orderNoOrEmail"
                            wire="orderNoOrEmail"
                            placeholder="{{ __('orders.query_order_no_or_email_placeholder') }}"
                            error="orderNoOrEmail"
                            class="px-4 py-3 mb-6"
                            autofocus
                        />
                    </x-widgets.form-field>

                    @if($errorMessage)
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800">{{ $errorMessage }}</p>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-800">{{ session('success') }}</p>
                        </div>
                    @endif

                    <x-widgets.button type="submit" class="w-full py-3 px-6">
                        {{ __('orders.query_send_verification_code') }}
                    </x-widgets.button>
                </form>
            </div>
        @endif

        <!-- 步骤2: 输入验证码 -->
        @if($step === 2)
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('orders.query_enter_verification_code') }}</h2>
                    <p class="text-gray-600">{{ __('orders.query_verification_code_hint') }}</p>
                </div>

                <form wire:submit.prevent="verifyCode">
                    <x-widgets.form-field 
                        :label="__('orders.query_verification_code')"
                        labelFor="verificationCode"
                        error="verificationCode"
                    >
                        <x-widgets.input 
                            type="text" 
                            id="verificationCode"
                            wire="verificationCode"
                            placeholder="000000"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            error="verificationCode"
                            class="px-4 py-3 text-center text-2xl font-mono tracking-widest"
                            autofocus
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        />
                    </x-widgets.form-field>

                    @if($errorMessage)
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800">{{ $errorMessage }}</p>
                        </div>
                    @endif

                    <div class="flex gap-4">
                        <x-widgets.button type="submit" class="flex-1 py-3 px-6">
                            {{ __('orders.query_verify') }}
                        </x-widgets.button>
                        <div x-data="{ countdown: @js($countdown) }" 
                             x-init="
                                if (countdown > 0) {
                                    let interval = setInterval(() => {
                                        countdown--;
                                        if (countdown <= 0) {
                                            clearInterval(interval);
                                        }
                                    }, 1000);
                                }
                             ">
                            <button 
                                type="button"
                                wire:click="resendCode"
                                :disabled="countdown > 0"
                                class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span x-show="countdown > 0">
                                    {{ __('orders.query_resend_code_countdown') }} (<span x-text="countdown"></span>
                                    @if(app()->getLocale() === 'zh_CN')
                                        秒
                                    @elseif(app()->getLocale() === 'ja')
                                        秒
                                    @elseif(app()->getLocale() === 'ko')
                                        초
                                    @elseif(app()->getLocale() === 'de')
                                        Sekunden
                                    @elseif(app()->getLocale() === 'es')
                                        segundos
                                    @elseif(app()->getLocale() === 'fr')
                                        secondes
                                    @elseif(app()->getLocale() === 'ru')
                                        секунд
                                    @else
                                        seconds
                                    @endif)
                                </span>
                                <span x-show="countdown <= 0">
                                    {{ __('orders.query_resend_code') }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <button 
                            type="button"
                            wire:click="$set('step', 1)"
                            class="text-sm text-teal-600 hover:text-teal-700 underline"
                        >
                            {{ __('orders.query_back') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- 步骤3: 显示订单详情 -->
        @if($step === 3 && $order)
            @php
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
                                            ? ($item->productVariant->getFirstMediaUrl('image', 'thumb') ?: asset('logo.svg'))
                                            : ($item->product ? ($item->product->getFirstMediaUrl('images', 'thumb') ?: asset('logo.svg')) : asset('logo.svg'));
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
                                        $productName = '-';
                                        if ($item->product) {
                                            $translation = $item->product->productTranslations
                                                ->where('language_id', $lang?->id)
                                                ->first();
                                            $productName = $translation?->name ?? $item->product->slug ?? '-';
                                        }
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
                        @if($order->shippingAddress)
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
                                        @if($order->shippingAddress->telephone)
                                            <p class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                                {{ $order->shippingAddress->telephone }}
                                            </p>
                                        @endif
                                        @if($order->shippingAddress->email)
                                            <p class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                {{ $order->shippingAddress->email }}
                                            </p>
                                        @endif
                                        <p>{{ $order->shippingAddress->address_1 }}</p>
                                        @if($order->shippingAddress->address_2)
                                            <p>{{ $order->shippingAddress->address_2 }}</p>
                                        @endif
                                        <p>
                                            {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->postcode }}
                                        </p>
                                        <p>
                                            {{ $order->shippingAddress->zone?->zoneTranslations->where('language_id', $lang?->id)->first()?->name ?? $order->shippingAddress->zone?->name ?? '' }}, 
                                            {{ $order->shippingAddress->country->countryTranslations->where('language_id', $lang?->id)->first()?->name ?? $order->shippingAddress->country->name ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                    </div>
                </div>

                <!-- 返回按钮 -->
                <div class="text-center">
                    <button 
                        wire:click="$set('step', 1)"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('orders.query_back_to_search') }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<x-seo-meta title="{{ __('orders.query_title') }}" description="{{ __('orders.query_description') }}" keywords="{{ __('orders.query_title') }}" />
