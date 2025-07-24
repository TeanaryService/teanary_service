<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">{{ __('orders.my_orders') }}</h1>

    @if ($orders->isEmpty())
        <p class="text-gray-500 text-center py-8">{{ __('orders.no_orders') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b">{{ __('orders.order_no') }}</th>
                        <th class="px-6 py-3 border-b">{{ __('orders.order_date') }}</th>
                        <th class="px-6 py-3 border-b">{{ __('orders.total') }}</th>
                        <th class="px-6 py-3 border-b">{{ __('orders.status') }}</th>
                        <th class="px-6 py-3 border-b">{{ __('orders.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="px-6 py-4 border-b">{{ $order->order_no }}</td>
                            <td class="px-6 py-4 border-b">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 border-b">{{ $order->currency->symbol }}{{ $order->total }}</td>
                            <td class="px-6 py-4 border-b">{{ __('orders.status_' . $order->status->value) }}</td>
                            <td class="px-6 py-4 border-b">
                                <a href="{{ locaRoute('user.orders.show', ['order' => $order]) }}"
                                    class="text-blue-600 hover:text-blue-800">
                                    {{ __('orders.view_details') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
