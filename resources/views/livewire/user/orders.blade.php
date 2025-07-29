@php
    $locale = session('lang');
    $localeService = app(\App\Services\LocaleCurrencyService::class);
    $lang = $localeService->getLanguageByCode($locale);
@endphp

<div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row gap-12 py-10">
    <div class="w-full md:w-1/4"> <x-profile-nav /></div>
    <div class="w-full md:w-3/4">
        <h2 class="text-2xl font-bold mb-6">{{ __('orders.my_orders') }}</h2>

        @if ($orders->isEmpty())
            <div class="bg-white p-6 rounded-xl shadow-md text-center">
                <p class="text-gray-500">{{ __('orders.no_orders') }}</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                    {{ __('orders.order_no') }}</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                    {{ __('orders.order_date') }}</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                    {{ __('orders.total') }}</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                    {{ __('orders.status') }}</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                    {{ __('orders.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($orders as $order)
                                @php
                                    $orderCurrency = $localeService->getCurrencies()->where('id', $order->currency_id)->first();
                                @endphp
                                <tr>
                                    <td class="px-6 py-4">{{ $order->order_no }}</td>
                                    <td class="px-6 py-4">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4">
                                        {{ $localeService->formatWithSymbol($order->total, $orderCurrency->code) }}</td>
                                    <td class="px-6 py-4">{{ $order->status->label() }}</td>
                                    <td class="px-6 py-4">
                                        <x-order-action-buttons :order="$order" :showDetails="true" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('orders.my_orders') }}" />
@endPushOnce
