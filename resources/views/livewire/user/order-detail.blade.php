@php
    $locale = session('lang');
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
@endphp

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">{{ __('orders.order_details') }}</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-semibold mb-3">{{ __('orders.order_no') }}: {{ $order->order_no }}</h2>
                <p>{{ __('orders.order_date') }}: {{ $order->created_at->format('Y-m-d H:i') }}</p>
                <p>{{ __('orders.status') }}: {{ __('orders.status_' . $order->status->value) }}</p>
                <p>{{ __('orders.payment_method') }}: {{ __('orders.payment_' . $order->payment_method->value) }}</p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-3">{{ __('orders.shipping_address') }}</h2>
                <p>{{ $order->shippingAddress->full_name }}</p>
                <p>{{ $order->shippingAddress->address }}</p>
                <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }}
                    {{ $order->shippingAddress->postcode }}</p>
                <p>{{ $order->shippingAddress->country }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">{{ __('orders.product_info') }}</h2>
        <table class="w-full">
            <thead>
                <tr>
                    <th class="text-left py-2">{{ __('orders.product_name') }}</th>
                    <th class="text-center py-2">{{ __('orders.quantity') }}</th>
                    <th class="text-right py-2">{{ __('orders.unit_price') }}</th>
                    <th class="text-right py-2">{{ __('orders.subtotal') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderItems as $item)
                    <tr>
                        <td class="py-2">
                            {{ $item->product->productTranslations->where('language_id', $lang->id)->first()->title }}
                            @if ($item->productVariant)
                                <br>
                                <span class="text-sm text-gray-500">{{ $item->productVariant->sku }}</span>
                            @endif
                        </td>
                        <td class="text-center py-2">{{ $item->qty }}</td>
                        <td class="text-right py-2">{{ $order->currency->symbol }}{{ $item->price }}</td>
                        <td class="text-right py-2">{{ $order->currency->symbol }}{{ $item->price * $item->qty }}</td>
                    </tr>
                @endforeach
                <tr class="border-t">
                    <td colspan="3" class="text-right py-2">{{ __('orders.shipping_fee') }}:</td>
                    <td class="text-right py-2">{{ $order->currency->symbol }}{{ $order->shipping_fee }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right py-2 font-bold">{{ __('orders.total') }}:</td>
                    <td class="text-right py-2 font-bold">{{ $order->currency->symbol }}{{ $order->total }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
