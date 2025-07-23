@php
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency');
@endphp

<div class="max-w-7xl mx-auto px-4 py-10 min-h-screen bg-white">
    <h1 class="text-3xl font-bold text-teal-700 mb-8">{{ __('app.checkout') }}</h1>

    @if (!empty($items))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- 左侧内容：地址 + 商品列表 -->
            <div class="md:col-span-2 space-y-8">

                {{-- 收货地址 --}}
                <div class="bg-white shadow rounded-xl p-6 space-y-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">{{ __('app.shipping_address') }}</h2>
                        <button wire:click="toggleAddressForm" type="button"
                            class="text-teal-600 hover:text-teal-700 font-medium">
                            {{ $showAddressForm ? __('app.cancel') : __('app.add_new_address') }}
                        </button>
                    </div>

                    @if ($showAddressForm)
                        <form wire:submit.prevent="saveAddress" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.email') }}</label>
                                <input type="email" wire:model="address.email"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.email')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.telephone') }}</label>
                                <input type="text" wire:model="address.telephone"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.telephone')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.firstname') }}</label>
                                <input type="text" wire:model="address.firstname"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.firstname')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.lastname') }}</label>
                                <input type="text" wire:model="address.lastname"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.lastname')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.company') }}</label>
                                <input type="text" wire:model="address.company"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.company')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.address_1') }}</label>
                                <input type="text" wire:model="address.address_1"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.address_1')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.address_2') }}</label>
                                <input type="text" wire:model="address.address_2"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.address_2')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.country') }}</label>
                                <select wire:model.live="address.country_id"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="">{{ __('app.select_country') }}</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('address.country_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.zone') }}</label>
                                <select wire:model="address.zone_id"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500"
                                    @if (!$address['country_id']) disabled @endif>
                                    <option value="">{{ __('app.select_zone') }}</option>
                                    @if ($zones)
                                        @foreach ($zones as $zone)
                                            <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('address.zone_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.city') }}</label>
                                <input type="text" wire:model="address.city"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.city')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">{{ __('app.postcode') }}</label>
                                <input type="text" wire:model="address.postcode"
                                    class="mt-1 p-3 w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('address.postcode')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <button type="submit"
                                    class="w-full py-3 text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition font-semibold">
                                    {{ __('app.save_address') }}
                                </button>
                            </div>
                            @if (session('error'))
                                <div class="md:col-span-2 text-red-600 text-sm mt-2">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </form>
                    @else
                        <div class="space-y-4">
                            @if ($addresses)
                                @foreach ($addresses as $address)
                                    <label
                                        class="flex items-start p-4 border rounded-lg hover:border-teal-500 transition cursor-pointer"
                                        :class="{ 'ring-2 ring-teal-500': shippingAddress == {{ $address->id }} }">
                                        <input type="radio" wire:model.live="shippingAddress"
                                            value="{{ $address->id }}" class="mt-1">
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
                </div>

                {{-- 商品列表 --}}
                <div class="bg-white shadow rounded-xl p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Order Items') }}</h2>
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
            </div>

            <!-- 右侧订单汇总 -->
            <div>
                <div class="bg-white shadow rounded-xl p-6 space-y-6">
                    <h2 class="text-xl font-semibold text-gray-800">{{ __('app.order_summary') }}</h2>
                    <div class="flex justify-between text-base font-semibold border-t pt-4">
                        <span>{{ __('app.subtotal') }}</span>
                        <span>{{ $currencyService->convertWithSymbol($orderPromotion ? ($total - $shippingFee) : ($total - $shippingFee), $currencyCode) }}</span>
                    </div>
                    @if ($orderPromotion)
                        <div class="flex justify-between text-base font-semibold">
                            <span>{{ __('app.promotion_discount') }}</span>
                            <span class="text-red-600">-{{ $currencyService->convertWithSymbol($orderPromotion['discount'], $currencyCode) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-semibold">
                        <span>{{ __('app.shipping_fee') }}</span>
                        <span>{{ $currencyService->convertWithSymbol($shippingFee, $currencyCode) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t pt-4">
                        <span>{{ __('app.total') }}</span>
                        <span class="text-teal-700 text-2xl">{{ $currencyService->convertWithSymbol($total, $currencyCode) }}</span>
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
                    <div class="mt-4">
                        <label class="block mb-2 font-semibold text-gray-700">{{ __('app.payment_method') }}</label>
                        <select wire:model="paymentMethod" class="w-full p-3 rounded-lg border-gray-300">
                            <option value="">{{ __('app.select_payment_method') }}</option>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->value }}">{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 配送方式选择 --}}
                    <div class="mt-4">
                        <label class="block mb-2 font-semibold text-gray-700">{{ __('app.shipping_method') }}</label>
                        <select wire:model="shippingMethod" wire:change="updatedShippingMethod($event.target.value)" class="w-full p-3 rounded-lg border-gray-300">
                            <option value="">{{ __('app.select_shipping_method') }}</option>
                            @foreach ($shippingMethods as $method)
                                <option value="{{ $method['value'] }}">
                                    {{ $method['label'] }}（{{ $method['description'] }}，+{{ $currencyService->convertWithSymbol($method['fee'], $currencyCode) }}）
                                </option>
                            @endforeach
                        </select>
                        @if ($shippingDescription)
                            <div class="text-xs text-gray-500 mt-1">{{ $shippingDescription }}</div>
                        @endif
                    </div>

                    <button wire:click="createOrder"
                        class="w-full py-3 bg-teal-600 text-white font-bold text-lg rounded-lg hover:bg-teal-700 transition shadow">
                        {{ __('app.place_order') }}
                    </button>
                </div>
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

@pushOnce('seo')
    <x-layouts.seo title="{{ __('app.checkout') }}" description="{{ __('app.checkout') }}"
        keywords="{{ __('app.checkout') }}" />
@endPushOnce
