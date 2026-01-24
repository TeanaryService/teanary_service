@php
    $orders = $this->orders;
    $users = $this->users;
    $currencies = $this->currencies;
    $statusOptions = $this->statusOptions;
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.OrderResource.pluralLabel') }}</h1>
                </div>
            </div>

            {{-- 筛选器 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- 搜索 --}}
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                            placeholder="{{ __('filament.order.order_no') }} / {{ __('filament.user.name') }} / {{ __('filament.user.email') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    {{-- 状态筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.status') }}</label>
                        <select wire:model.live="statusFilter" multiple
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 用户筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.user_id') }}</label>
                        <select wire:model.live="userIdFilter"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 货币筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.currency_id') }}</label>
                        <select wire:model.live="currencyIdFilter"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 日期范围 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.created_from') }}</label>
                        <input type="date" wire:model.live="createdFrom"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.order.created_until') }}</label>
                        <input type="date" wire:model.live="createdUntil"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    {{-- 重置按钮 --}}
                    <div class="flex items-end">
                        <button wire:click="resetFilters" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            {{ __('app.reset') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- 订单列表 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.order.order_no') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.order.user_id') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.order.status') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.order.total') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.order.items_count') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('orders.created_at') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('app.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $order->order_no }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $order->user?->name ?? '-' }}
                                        </div>
                                        @if($order->user?->email)
                                            <div class="text-xs text-gray-500">{{ $order->user->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                \App\Enums\OrderStatusEnum::Pending->value => 'bg-gray-100 text-gray-800',
                                                \App\Enums\OrderStatusEnum::Paid->value => 'bg-blue-100 text-blue-800',
                                                \App\Enums\OrderStatusEnum::Shipped->value => 'bg-yellow-100 text-yellow-800',
                                                \App\Enums\OrderStatusEnum::Completed->value => 'bg-green-100 text-green-800',
                                                \App\Enums\OrderStatusEnum::Cancelled->value => 'bg-red-100 text-red-800',
                                            ];
                                            $color = $statusColors[$order->status->value] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ ($order->currency?->symbol ?? '') . number_format($order->total, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm text-gray-900">{{ $order->order_items_count }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order->created_at->format('Y-m-d H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ locaRoute('manager.orders.show', ['order' => $order->id]) }}" 
                                           class="text-teal-600 hover:text-teal-900">
                                            {{ __('app.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ __('app.no_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 分页 --}}
                @if($orders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-manager.layout>
</div>
