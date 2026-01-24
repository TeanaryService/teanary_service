@php
    $userStats = $this->userStats;
    $orderStats = $this->orderStats;
    $topProducts = $this->topProducts;
    $localeService = app(\App\Services\LocaleCurrencyService::class);
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.dashboard.heading') }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ __('filament.dashboard.title') }}</p>
            </div>

            {{-- 用户统计卡片 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('filament.dashboard.stats.new_users_today') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($userStats['users_today']) }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                <span class="{{ $userStats['users_today_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $userStats['users_today_diff'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($userStats['users_today_diff']), 1) }}%
                                </span>
                                {{ __('filament.dashboard.stats.new_users_today_desc', ['change' => '']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('filament.dashboard.stats.total_users') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($userStats['total_users']) }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                <span class="{{ $userStats['total_users_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $userStats['total_users_diff'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($userStats['total_users_diff']), 1) }}%
                                </span>
                                {{ __('filament.dashboard.stats.total_users_desc', ['change' => '']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('filament.dashboard.stats.active_users') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($userStats['active_users']) }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                <span class="{{ $userStats['active_users_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $userStats['active_users_diff'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($userStats['active_users_diff']), 1) }}%
                                </span>
                                {{ __('filament.dashboard.stats.active_users_desc', ['change' => '']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('filament.dashboard.stats.avg_order_value') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $localeService->convertWithSymbol($userStats['avg_order_value'], $userStats['currency_code']) }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('filament.dashboard.stats.avg_order_value_desc') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 订单统计卡片 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('filament.dashboard.stats.orders_today') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($orderStats['orders_today']) }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                <span class="{{ $orderStats['orders_today_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $orderStats['orders_today_diff'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($orderStats['orders_today_diff']), 1) }}%
                                </span>
                                {{ __('filament.dashboard.stats.orders_today_desc', ['change' => '']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('filament.dashboard.stats.revenue_today') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $localeService->convertWithSymbol($orderStats['revenue_today'], $orderStats['currency_code']) }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                <span class="{{ $orderStats['revenue_today_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $orderStats['revenue_today_diff'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($orderStats['revenue_today_diff']), 1) }}%
                                </span>
                                {{ __('filament.dashboard.stats.revenue_today_desc', ['change' => '']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('filament.dashboard.stats.pending_orders') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($orderStats['pending_orders']) }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('filament.dashboard.stats.pending_orders_desc') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('filament.dashboard.stats.revenue_month') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $localeService->convertWithSymbol($orderStats['revenue_month'], $orderStats['currency_code']) }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                <span class="{{ $orderStats['revenue_month_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $orderStats['revenue_month_diff'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($orderStats['revenue_month_diff']), 1) }}%
                                </span>
                                {{ __('filament.dashboard.stats.revenue_month_desc', ['change' => '']) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 热门商品 --}}
            <div class="grid grid-cols-1 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.dashboard.widgets.top_products') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('filament.dashboard.widgets.product_name') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('filament.dashboard.widgets.total_quantity') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('filament.dashboard.widgets.total_revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topProducts as $index => $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $product['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($product['total_qty']) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-900 font-medium">{{ $localeService->convertWithSymbol($product['total_revenue'], $orderStats['currency_code']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('filament.dashboard.widgets.no_products') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </x-manager.layout>
</div>
