@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('orders', __('manager.orders.label'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="orders" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('manager.orders.label') }}</h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                {{-- 筛选器 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input 
                                type="text" 
                                wire="live.debounce.300ms=search"
                                placeholder="订单号、用户名称、邮箱"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.status') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterStatus" 
                                :options="$statusOptions"
                                :multiple="false"
                                size="3"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.user') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterUserId" 
                                :options="[['value' => '', 'label' => __('app.all')], ...collect($users)->map(fn($user) => ['value' => $user->id, 'label' => $user->name . ' (' . $user->email . ')'])->toArray()]"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.currency_id') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterCurrencyId" 
                                :options="[['value' => '', 'label' => __('app.all')], ...collect($currencies)->map(fn($currency) => ['value' => $currency->id, 'label' => $currency->name . ' (' . $currency->symbol . ')'])->toArray()]"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.created_from') }}</x-widgets.label>
                            <x-widgets.input 
                                type="date" 
                                wire="live=createdFrom"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.order.created_until') }}</x-widgets.label>
                            <x-widgets.input 
                                type="date" 
                                wire="live=createdUntil"
                            />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-widgets.button 
                            wire:click="resetFilters"
                            variant="secondary"
                        >
                            {{ __('app.reset') }}
                        </x-widgets.button>
                    </div>
                </div>

                {{-- 订单列表 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.order.order_no') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.order.user') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.order.status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.order.total') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.order.items_count') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.created_at') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($orders as $order)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $order->order_no }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($order->user)
                                                <div class="text-sm text-gray-900">{{ $order->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $order->user->email }}</div>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusBadgeColor($order->status) }}">
                                                {{ $order->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $order->currency ? $order->currency->symbol : '' }}{{ number_format($order->total, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                            {{ $order->order_items_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ locaRoute('manager.orders.detail', ['id' => $order->id]) }}" 
                                               class="text-teal-600 hover:text-teal-700">
                                                {{ __('app.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-10 h-10 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span>{{ __('app.no_data') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- 分页 --}}
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('manager.orders.label') }}" />
