@php
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency');
@endphp

<div class="max-w-7xl mx-auto px-4 py-10 min-h-[60vh] bg-gray-50">
    {{-- 添加错误提示 --}}
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <x-widgets.page-title 
        :title="__('app.checkout')"
        class="mb-8 text-teal-700"
    />

    @if (!empty($items))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- 左侧内容：地址 + 商品列表 -->
            <div class="md:col-span-2 space-y-8">

                {{-- 收货地址 --}}
                <x-widgets.card class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">{{ __('app.shipping_address') }}</h2>
                        <button wire:click="toggleAddressForm" type="button"
                            class="text-teal-600 hover:text-teal-700 font-medium">
                            {{ $showAddressForm ? __('app.cancel') : __('app.add_new_address') }}
                        </button>
                    </div>

                    @if ($showAddressForm)
                        <form wire:submit.prevent="saveAddress">
                            <x-widgets.form-container class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-widgets.form-field :label="__('app.email')" error="address.email">
                                <x-widgets.input type="email" wire="address.email" error="address.email" class="p-3" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.telephone')" error="address.telephone">
                                <x-widgets.input type="text" wire="address.telephone" error="address.telephone" class="p-3" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.firstname')" error="address.firstname">
                                <x-widgets.input type="text" wire="address.firstname" error="address.firstname" class="p-3" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.lastname')" error="address.lastname">
                                <x-widgets.input type="text" wire="address.lastname" error="address.lastname" class="p-3" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.company')" error="address.company">
                                <x-widgets.input type="text" wire="address.company" error="address.company" class="p-3" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.address_1')" error="address.address_1">
                                <x-widgets.input type="text" wire="address.address_1" error="address.address_1" class="p-3" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.address_2')" error="address.address_2">
                                <x-widgets.input type="text" wire="address.address_2" error="address.address_2" class="p-3" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.country')" error="address.country_id">
                                <x-widgets.select 
                                    wire="live=address.country_id" 
                                    :options="[['value' => '', 'label' => __('app.select_country')], ...collect($countries)->map(fn($c) => ['value' => $c['id'], 'label' => $c['name']])->toArray()]"
                                    error="address.country_id"
                                    class="p-3"
                                />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.zone')" error="address.zone_id">
                                <x-widgets.select 
                                    wire="address.zone_id" 
                                    :options="[['value' => '', 'label' => __('app.select_zone')], ...($zones ? collect($zones)->map(fn($z) => ['value' => $z['id'], 'label' => $z['name']])->toArray() : [])]"
                                    error="address.zone_id"
                                    class="p-3"
                                    :disabled="!$address['country_id']"
                                />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.city')" error="address.city">
                                <x-widgets.input type="text" wire="address.city" error="address.city" class="p-3" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('app.postcode')" error="address.postcode">
                                <x-widgets.input type="text" wire="address.postcode" error="address.postcode" class="p-3" />
                            </x-widgets.form-field>

                                <div class="md:col-span-2">
                                    <x-widgets.button 
                                        type="submit"
                                        size="lg"
                                        class="w-full"
                                    >
                                        {{ __('app.save_address') }}
                                    </x-widgets.button>
                                </div>
                                @if (session('error'))
                                    <div class="md:col-span-2 text-red-600 text-sm mt-2">
                                        {{ session('error') }}
                                    </div>
                                @endif
                            </x-widgets.form-container>
                        </form>
                    @else
                        <div class="space-y-4">
                            @if ($addresses)
                                @foreach ($addresses as $address)
                                    <label
                                        class="{{ $shippingAddress == $address->id ? 'ring-2 ring-teal-500' : '' }} flex items-start p-4 border rounded-lg hover:border-teal-500 transition cursor-pointer">
                                        <x-widgets.radio 
                                            wire="live=shippingAddress"
                                            :value="$address->id"
                                            :checked="$shippingAddress == $address->id"
                                            class="!gap-0 mt-1"
                                        />
                                        <div class="ml-4 space-y-1">
                                            <div class="font-medium text-gray-900">{{ $address->firstname }}
                                                {{ $address->lastname }}</div>
                                            <div class="text-sm text-gray-600">{{ $address->address_1 }},
                                                {{ $address->city }}, {{ $address->zone_name }},
                                                {{ $address->country_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $address->telephone }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            @else
                                <div class="text-center text-gray-500 py-6">
                                    {{ __('app.no_addresses') }}
                                </div>
                            @endif
                        </div>
                    @endif
                </x-widgets.card>

                {{-- 商品列表 --}}
                <x-widgets.card>
                    <x-widgets.section-title 
                        :title="__('Order Items')"
                        class="mb-4"
                    />
                    <div class="overflow-x-auto">
                        <table class="w-full border-separate border-spacing-y-2 text-sm">
                        <thead class="text-gray-600">
                            <tr>
                                <th class="px-2 py-1">{{ __('app.product') }}</th>
                                <th class="px-2 py-1">{{ __('app.specification') }}</th>
                                <th class="px-2 py-1">{{ __('app.price') }}</th>
                                <th class="px-2 py-1">{{ __('app.qty') }}</th>
                                <th class="px-2 py-1">{{ __('app.subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr class="bg-gray-50">
                                    <td class="px-2 py-2 flex items-center gap-3">
                                        <img src="{{ $item['image'] }}" class="w-12 h-12 rounded-lg border"
                                            alt="">
                                        <span class="text-gray-900 font-medium">{{ $item['product_name'] }}</span>
                                    </td>
                                    <td class="px-2 py-2 text-gray-500">{{ $item['specs'] }}</td>
                                    <td class="px-2 py-2 text-teal-700 font-semibold">
                                        @if ($item['promotion'])
                                            <span>{{ $currencyService->convertWithSymbol($item['price'], $currencyCode) }}</span>
                                            <span
                                                class="text-gray-400 line-through ml-2">{{ $currencyService->convertWithSymbol($item['original_price'], $currencyCode) }}</span>
                                            <span class="ml-2 text-xs text-red-500 font-semibold">
                                                {{ $item['promotion']['rule']['discount_type'] == 'percent' ? __('app.discount_percent', ['percent' => $item['promotion']['rule']['discount_value']]) : __('app.discount_amount', ['amount' => $currencyService->convertWithSymbol($item['promotion']['discount'], $currencyCode)]) }}
                                            </span>
                                        @else
                                            <span>{{ $currencyService->convertWithSymbol($item['price'], $currencyCode) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2">{{ $item['qty'] }}</td>
                                    <td class="px-2 py-2 text-teal-700 font-semibold">
                                        {{ $currencyService->convertWithSymbol($item['subtotal'], $currencyCode) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <div class="text-gray-300 mt-4">{{ __('app.checkout_support_message') }}</div>
                </x-widgets.card>
            </div>

            <!-- 右侧订单汇总 -->
            <div>
                <x-widgets.card class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-800">{{ __('app.order_summary') }}</h2>
                    <div class="flex justify-between text-base font-semibold border-t pt-4">
                        <span>{{ __('app.subtotal') }}</span>
                        <span>{{ $currencyService->convertWithSymbol($orderPromotion ? $total - $shippingFee : $total - $shippingFee, $currencyCode) }}</span>
                    </div>
                    @if ($orderPromotion)
                        <div class="flex justify-between text-base font-semibold">
                            <span>{{ __('app.promotion_discount') }}</span>
                            <span
                                class="text-red-600">-{{ $currencyService->convertWithSymbol($orderPromotion['discount'], $currencyCode) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-semibold">
                        <span>{{ __('app.shipping_fee') }}</span>
                        <span>{{ $currencyService->convertWithSymbol($shippingFee, $currencyCode) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t pt-4">
                        <span>{{ __('app.total') }}</span>
                        <span
                            class="text-teal-700 text-2xl">{{ $currencyService->convertWithSymbol($total, $currencyCode) }}</span>
                    </div>
                    @if ($orderPromotion)
                        <div class="mt-2 p-3 bg-red-50 border-l-4 border-red-400 rounded text-red-700">
                            <div class="font-semibold">{{ $orderPromotion['name'] }}</div>
                            <div class="text-xs">{{ $orderPromotion['description'] }}</div>
                            <div class="mt-1">
                                {{ $orderPromotion['rule']['discount_type'] == 'percent'
                                    ? __('app.discount_percent', ['percent' => $orderPromotion['rule']['discount_value']])
                                    : __('app.discount_amount', [
                                        'amount' => $currencyService->convertWithSymbol($orderPromotion['discount'], $currencyCode),
                                    ]) }}
                            </div>
                        </div>
                    @endif

                    {{-- 支付方式选择 --}}
                    <x-widgets.form-field :label="__('app.payment_method')">
                        <div wire:init="initCheckoutMethods">
                            @if ($loadingPaymentMethods)
                                <div class="animate-pulse flex space-x-4">
                                    <div class="h-12 bg-slate-200 rounded-xl w-full"></div>
                                </div>
                            @else
                                <x-widgets.select 
                                    wire="paymentMethod" 
                                    :options="[['value' => '', 'label' => __('app.select_payment_method')], ...collect($paymentMethods)->map(fn($method) => ['value' => $method->value, 'label' => $method->label()])->toArray()]"
                                />
                            @endif
                        </div>
                    </x-widgets.form-field>

                    {{-- 配送方式选择 --}}
                    <x-widgets.form-field :label="__('app.shipping_method')">
                        @if ($loadingShippingMethods)
                            <div class="animate-pulse flex space-x-4">
                                <div class="h-12 bg-slate-200 rounded-xl w-full"></div>
                            </div>
                        @else
                            <x-widgets.select 
                                wire="shippingMethod"
                                wire:change="changeShippingMethod($event.target.value)"
                                :options="[['value' => '', 'label' => __('app.select_shipping_method')], ...collect($shippingMethods)->map(function($method) use ($currencyService, $currencyCode) {
                                    return [
                                        'value' => $method['value'],
                                        'label' => $method['label'] . '（' . $method['description'] . '，+' . $currencyService->convertWithSymbol($method['fee'], $currencyCode) . '）'
                                    ];
                                })->toArray()]"
                            />
                            @if ($shippingDescription)
                                <p class="mt-2 text-xs text-gray-500">{{ $shippingDescription }}</p>
                            @endif
                        @endif
                    </x-widgets.form-field>

                    <x-widgets.button 
                        wire:click="createOrder"
                        size="lg"
                        class="w-full"
                    >
                        {{ __('app.place_order') }}
                    </x-widgets.button>
                </x-widgets.card>
            </div>
        </div>
    @else
        <div class="text-center py-10">
            <p class="text-gray-500">{{ __('app.no_items_to_checkout') }}</p>
            <a href="{{ locaRoute('cart') }}" class="text-teal-600 hover:underline mt-2 inline-block">
                {{ __('app.return_to_cart') }}
            </a>
        </div>
    @endif
</div>

<x-seo-meta title="{{ __('app.checkout') }}" />
