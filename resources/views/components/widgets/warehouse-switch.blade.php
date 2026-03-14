@php
    if (!isset($warehouses) || !isset($selectedWarehouse)) {
        $service = app(\App\Services\WarehouseService::class);
        $warehouses = $service->getWarehouses();
        $warehouseId = session('warehouse_id');
        $selectedWarehouse = $warehouseId ? $service->getWarehouseById($warehouseId) : $service->getDefaultWarehouse();
    }
@endphp

@if($warehouses->isNotEmpty())
<div x-data="{ open: false }" class="relative">
    <button @click="open = ! open"
        class="flex items-center gap-2 px-2 md:px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 h-10">
        <x-heroicon-o-building-storefront class="w-5 h-5" />
        <div class="hidden lg:block">{{ $selectedWarehouse->name ?? __('app.warehouse') }}</div>
        <x-heroicon-o-chevron-down class="w-4 h-4 hidden md:block" />
    </button>
    <div x-show="open" @click.away="open = false"
        class="absolute right-0 mt-2 w-48 bg-white border border-teal-200 rounded-lg shadow-lg z-50 max-h-64 overflow-y-auto">
        @foreach ($warehouses as $wh)
            <div @click.prevent="
                    document.getElementById('warehouse-input').value = '{{ $wh->id }}';
                    document.getElementById('lang-currency-form').submit();
                "
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-teal-100 cursor-pointer">
                {{ $wh->name }}
                @if($wh->is_default)
                    <span class="text-xs text-teal-600">({{ __('app.default') }})</span>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
