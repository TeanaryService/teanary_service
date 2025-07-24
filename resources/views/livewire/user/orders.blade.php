<div class="max-w-3xl mx-auto py-10 px-4">
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
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">{{ __('orders.order_no') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">{{ __('orders.order_date') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">{{ __('orders.total') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">{{ __('orders.status') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">{{ __('orders.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-6 py-4">{{ $order->order_no }}</td>
                                <td class="px-6 py-4">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4">{{ $order->currency->symbol }}{{ $order->total }}</td>
                                <td class="px-6 py-4">{{ __('orders.status_' . $order->status->value) }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ locaRoute('user.orders.show', ['order' => $order]) }}"
                                        class="text-teal-600 hover:text-teal-800">
                                        {{ __('orders.view_details') }}
                                    </a>
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

@pushOnce('seo')
    <x-layouts.seo title="{{ __('orders.my_orders') }}"/>
@endPushOnce
