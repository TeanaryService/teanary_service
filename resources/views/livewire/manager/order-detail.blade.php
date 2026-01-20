@php
    $order = $this->order;
    $users = $this->users;
    $currencies = $this->currencies;
    $shippingAddresses = $this->shippingAddresses;
    $billingAddresses = $this->billingAddresses;
    $statusOptions = $this->statusOptions;
    $paymentMethodOptions = $this->paymentMethodOptions;
    $shippingMethodOptions = $this->shippingMethodOptions;
    $localeService = app(\App\Services\LocaleCurrencyService::class);
    $lang = $localeService->getLanguageByCode(app()->getLocale());
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.OrderResource.label') }} #{{ $order->order_no }}</h1>
                </div>
                <a href="{{ locaRoute('manager.orders') }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    {{ __('app.back') }}
                </a>
            </div>

            @if(session('message'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('message') }}
                </div>
            @endif

            {{-- 订单基本信息编辑表单 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.order.basic_info') }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.order_no') }}</label>
                            <input type="text" value="{{ $order->order_no }}" disabled
                                class="w-full rounded-lg border-gray-300 bg-gray-50">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.status') }} <span class="text-red-500">*</span></label>
                            <select wire:model="status" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.user_id') }}</label>
                            <select wire:model.live="userId" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <option value="">{{ __('app.not_available') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('userId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.currency_id') }}</label>
                            <select wire:model="currencyId" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <option value="">{{ __('app.not_available') }}</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->code }})</option>
                                @endforeach
                            </select>
                            @error('currencyId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.payment_method') }}</label>
                            <select wire:model="paymentMethod" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <option value="">{{ __('app.not_available') }}</option>
                                @foreach($paymentMethodOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('paymentMethod') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.shipping_method') }}</label>
                            <select wire:model="shippingMethod" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <option value="">{{ __('app.not_available') }}</option>
                                @foreach($shippingMethodOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('shippingMethod') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.shipping_address_id') }}</label>
                            <select wire:model="shippingAddressId" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                @if(!$userId) disabled @endif>
                                <option value="">{{ __('app.not_available') }}</option>
                                @foreach($shippingAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->firstname }} {{ $address->lastname }} ({{ $address->address_1 }}, {{ $address->city }})
                                    </option>
                                @endforeach
                            </select>
                            @error('shippingAddressId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.billing_address_id') }}</label>
                            <select wire:model="billingAddressId" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                @if(!$userId) disabled @endif>
                                <option value="">{{ __('app.not_available') }}</option>
                                @foreach($billingAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->firstname }} {{ $address->lastname }} ({{ $address->address_1 }}, {{ $address->city }})
                                    </option>
                                @endforeach
                            </select>
                            @error('billingAddressId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.total') }} <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    {{ $order->currency?->symbol ?? '¥' }}
                                </span>
                                <input type="number" wire:model="total" step="0.01" min="0" required
                                    class="w-full pl-8 rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                            @error('total') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" 
                            class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                            {{ __('app.save') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- 订单商品 --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('filament.order.order_items') }}</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @forelse($order->orderItems as $item)
                                @php
                                    $productTranslation = $item->product?->productTranslations
                                        ->where('language_id', $lang?->id)
                                        ->first() 
                                        ?? $item->product?->productTranslations->first();
                                    $productName = $productTranslation?->name ?? $item->product?->slug ?? '-';
                                    
                                    $variantSpecs = [];
                                    if ($item->productVariant) {
                                        foreach ($item->productVariant->specificationValues as $specValue) {
                                            $specTranslation = $specValue->specificationValueTranslations
                                                ->where('language_id', $lang?->id)
                                                ->first()
                                                ?? $specValue->specificationValueTranslations->first();
                                            $variantSpecs[] = $specTranslation?->name ?? '';
                                        }
                                    }
                                @endphp
                                <div class="px-6 py-4">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ $productName }}</div>
                                            @if(!empty($variantSpecs))
                                                <div class="text-sm text-gray-500 mt-1">
                                                    {{ implode(' / ', array_filter($variantSpecs)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-gray-600">
                                                {{ ($order->currency?->symbol ?? '') . number_format($item->price, 2) }} × {{ $item->qty }}
                                            </div>
                                            <div class="text-sm font-medium text-gray-900 mt-1">
                                                {{ ($order->currency?->symbol ?? '') . number_format($item->price * $item->qty, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-12 text-center text-sm text-gray-500">
                                    {{ __('app.no_data') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- 订单汇总和地址信息 --}}
                <div class="space-y-6">
                    {{-- 订单汇总 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('orders.order_summary') }}</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('orders.subtotal') }}</span>
                                <span class="text-gray-900">{{ ($order->currency?->symbol ?? '') . number_format($order->orderItems->sum(fn($item) => $item->price * $item->qty), 2) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 flex justify-between">
                                <span class="font-semibold text-gray-900">{{ __('orders.total') }}</span>
                                <span class="font-bold text-teal-600 text-lg">{{ ($order->currency?->symbol ?? '') . number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 收货地址 --}}
                    @if($order->shippingAddress)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.order.shipping_address_id') }}</h3>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>{{ $order->shippingAddress->firstname }} {{ $order->shippingAddress->lastname }}</div>
                                <div>{{ $order->shippingAddress->address_1 }}</div>
                                @if($order->shippingAddress->address_2)
                                    <div>{{ $order->shippingAddress->address_2 }}</div>
                                @endif
                                <div>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->zone?->name ?? '' }} {{ $order->shippingAddress->postcode }}</div>
                                <div>{{ $order->shippingAddress->country?->name ?? '' }}</div>
                                @if($order->shippingAddress->telephone)
                                    <div class="mt-2">{{ __('app.telephone') }}: {{ $order->shippingAddress->telephone }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- 账单地址 --}}
                    @if($order->billingAddress)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.order.billing_address_id') }}</h3>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>{{ $order->billingAddress->firstname }} {{ $order->billingAddress->lastname }}</div>
                                <div>{{ $order->billingAddress->address_1 }}</div>
                                @if($order->billingAddress->address_2)
                                    <div>{{ $order->billingAddress->address_2 }}</div>
                                @endif
                                <div>{{ $order->billingAddress->city }}, {{ $order->billingAddress->zone?->name ?? '' }} {{ $order->billingAddress->postcode }}</div>
                                <div>{{ $order->billingAddress->country?->name ?? '' }}</div>
                                @if($order->billingAddress->telephone)
                                    <div class="mt-2">{{ __('app.telephone') }}: {{ $order->billingAddress->telephone }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- 物流信息 --}}
                    @if($order->orderShipments->isNotEmpty())
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.order.order_shipments') }}</h3>
                            <div class="space-y-4">
                                @foreach($order->orderShipments as $shipment)
                                    <div class="border-l-4 border-teal-500 pl-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $shipment->shipping_method?->label() ?? '-' }}
                                        </div>
                                        @if($shipment->tracking_number)
                                            <div class="text-sm text-gray-600 mt-1">
                                                {{ __('filament.order_shipment.tracking_number') }}: {{ $shipment->tracking_number }}
                                            </div>
                                        @endif
                                        @if($shipment->notes)
                                            <div class="text-sm text-gray-500 mt-1">{{ $shipment->notes }}</div>
                                        @endif
                                        <div class="text-xs text-gray-400 mt-1">{{ $shipment->created_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-manager.layout>
</div>
