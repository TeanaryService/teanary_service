@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('traffic-statistics', __('filament.TrafficStatistics.navigation_label'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="traffic-statistics" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.TrafficStatistics.navigation_label') }}</h1>
                </div>

                {{-- 筛选器 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('filament.TrafficStatistics.date_range') }}
                            </label>
                            <select 
                                wire:model.live="dateRange" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            >
                                <option value="today">{{ __('filament.TrafficStatistics.today') }}</option>
                                <option value="yesterday">{{ __('filament.TrafficStatistics.yesterday') }}</option>
                                <option value="7days">{{ __('filament.TrafficStatistics.7days') }}</option>
                                <option value="30days">{{ __('filament.TrafficStatistics.30days') }}</option>
                                <option value="90days">{{ __('filament.TrafficStatistics.90days') }}</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('filament.TrafficStatistics.visitor_type') }}
                            </label>
                            <select 
                                wire:model.live="visitorType" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            >
                                <option value="all">{{ __('filament.TrafficStatistics.all') }}</option>
                                <option value="human">{{ __('filament.TrafficStatistics.human') }}</option>
                                <option value="bot">{{ __('filament.TrafficStatistics.bot') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 统计卡片 --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- 总访问量 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('filament.TrafficStatistics.total_visits') }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_visits']) }}</p>
                            </div>
                            <div class="text-teal-500">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- 页面浏览量 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('filament.TrafficStatistics.total_page_views') }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_page_views']) }}</p>
                            </div>
                            <div class="text-blue-500">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- 独立IP -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('filament.TrafficStatistics.unique_ips') }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['unique_ips']) }}</p>
                            </div>
                            <div class="text-green-500">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- 独立页面 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('filament.TrafficStatistics.unique_pages') }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['unique_pages']) }}</p>
                            </div>
                            <div class="text-purple-500">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 详细统计 --}}
                @if($this->visitorType === 'all')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- 真人访问统计 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.TrafficStatistics.human') }}</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('filament.TrafficStatistics.visits') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['human_visits']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('filament.TrafficStatistics.page_views_count') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['human_page_views']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('filament.TrafficStatistics.unique_ips') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['human_unique_ips']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 爬虫访问统计 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.TrafficStatistics.bot') }}</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('filament.TrafficStatistics.visits') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['bot_visits']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('filament.TrafficStatistics.page_views_count') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['bot_page_views']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('filament.TrafficStatistics.unique_ips') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['bot_unique_ips']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 热门页面 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('filament.TrafficStatistics.top_pages') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.TrafficStatistics.rank') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.TrafficStatistics.page_path') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.TrafficStatistics.visits') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.TrafficStatistics.page_views_count') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topPages as $index => $page)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #{{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">{{ $page['path'] }}</code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">
                                            {{ number_format($page['total_visits']) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">
                                            {{ number_format($page['page_views']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-10 h-10 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span>{{ __('filament.TrafficStatistics.no_data') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
