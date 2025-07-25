@php
    $locale = session('lang');
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
@endphp

<div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row gap-12 py-10">
    <div class="w-1/4"> <x-profile-nav /></div>
    <div class="w-3/4">
        <h2 class="text-2xl font-bold mb-6">{{ __('orders.order_details') }}</h2>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('orders.order_info') }}</h3>
                        <div class="text-gray-600">
                            <p class="flex justify-between">
                                <span>{{ __('orders.order_no') }}:</span>
                                <span class="font-medium text-gray-900">{{ $order->order_no }}</span>
                            </p>
                            <p class="flex justify-between">
                                <span>{{ __('orders.order_date') }}:</span>
                                <span>{{ $order->created_at->format('Y-m-d H:i') }}</span>
                            </p>
                            <p class="flex justify-between">
                                <span>{{ __('orders.status') }}:</span>
                                <span class="text-teal-600">{{ __('orders.status_' . $order->status->value) }}</span>
                            </p>
                            <p class="flex justify-between">
                                <span>{{ __('orders.payment_method') }}:</span>
                                <span>{{ __('orders.payment_' . $order->payment_method->value) }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('orders.shipping_address') }}</h3>
                        <div class="text-gray-600">
                            <p>{{ $order->shippingAddress->full_name }}</p>
                            <p>{{ $order->shippingAddress->address }}</p>
                            <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }}
                                {{ $order->shippingAddress->postcode }}</p>
                            <p>{{ $order->shippingAddress->country }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('orders.product_info') }}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 text-sm font-medium text-gray-700">
                                    {{ __('orders.product_name') }}</th>
                                <th class="text-center py-3 text-sm font-medium text-gray-700">
                                    {{ __('orders.quantity') }}</th>
                                <th class="text-right py-3 text-sm font-medium text-gray-700">
                                    {{ __('orders.unit_price') }}</th>
                                <th class="text-right py-3 text-sm font-medium text-gray-700">
                                    {{ __('orders.subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($order->orderItems as $item)
                                <tr>
                                    <td class="py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $item->product->productTranslations->where('language_id', $lang->id)->first()->title }}
                                        </div>
                                        @if ($item->productVariant)
                                            <div class="text-sm text-gray-500">{{ $item->productVariant->sku }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center py-4">{{ $item->qty }}</td>
                                    <td class="text-right py-4">{{ $order->currency->symbol }}{{ $item->price }}</td>
                                    <td class="text-right py-4">
                                        {{ $order->currency->symbol }}{{ $item->price * $item->qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="text-right py-4">{{ __('orders.shipping_fee') }}:</td>
                                <td class="text-right py-4">{{ $order->currency->symbol }}{{ $order->shipping_fee }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right py-4 font-semibold">{{ __('orders.total') }}:</td>
                                <td class="text-right py-4 font-semibold text-teal-600">
                                    {{ $order->currency->symbol }}{{ $order->total }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('orders.order_details') }}" />
@endPushOnce
