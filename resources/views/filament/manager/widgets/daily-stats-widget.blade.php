<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Widget content --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <div class="text-lg font-bold text-gray-700 mb-2">{{ __('manager.stats_users') }}</div>
                <div class="text-3xl font-extrabold text-primary-600">{{ $stats['users_today'] }}</div>
                <div class="text-sm text-gray-400 mt-1">{{ __('manager.stats_yesterday') }}:
                    {{ $stats['users_yesterday'] }}
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <div class="text-lg font-bold text-gray-700 mb-2">{{ __('manager.stats_orders') }}</div>
                <div class="text-3xl font-extrabold text-primary-600">{{ $stats['orders_today'] }}</div>
                <div class="text-sm text-gray-400 mt-1">{{ __('manager.stats_yesterday') }}:
                    {{ $stats['orders_yesterday'] }}
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <div class="text-lg font-bold text-gray-700 mb-2">{{ __('manager.stats_cart_items') }}</div>
                <div class="text-3xl font-extrabold text-primary-600">{{ $stats['cart_items_today'] }}</div>
                <div class="text-sm text-gray-400 mt-1">{{ __('manager.stats_yesterday') }}:
                    {{ $stats['cart_items_yesterday'] }}</div>
            </div>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
