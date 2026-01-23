@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('traffic-statistics', __('manager.traffic_statistics.navigation_label'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[80vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="traffic-statistics" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('manager.traffic_statistics.navigation_label') }}</h1>
                </div>

                {{-- 筛选器 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <x-widgets.label>{{ __('manager.traffic_statistics.date_range') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=dateRange" 
                                :options="[
                                    ['value' => 'today', 'label' => __('manager.traffic_statistics.today')],
                                    ['value' => 'yesterday', 'label' => __('manager.traffic_statistics.yesterday')],
                                    ['value' => '7days', 'label' => __('manager.traffic_statistics.7days')],
                                    ['value' => '30days', 'label' => __('manager.traffic_statistics.30days')],
                                    ['value' => '90days', 'label' => __('manager.traffic_statistics.90days')]
                                ]"
                            />
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <x-widgets.label>{{ __('manager.traffic_statistics.visitor_type') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=visitorType" 
                                :options="[
                                    ['value' => 'all', 'label' => __('manager.traffic_statistics.all')],
                                    ['value' => 'human', 'label' => __('manager.traffic_statistics.human')],
                                    ['value' => 'bot', 'label' => __('manager.traffic_statistics.bot')]
                                ]"
                            />
                        </div>
                    </div>
                </div>

                {{-- 统计卡片 --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- 总访问量 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('manager.traffic_statistics.total_visits') }}</p>
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
                                <p class="text-sm font-medium text-gray-500">{{ __('manager.traffic_statistics.total_page_views') }}</p>
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
                                <p class="text-sm font-medium text-gray-500">{{ __('manager.traffic_statistics.unique_ips') }}</p>
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
                                <p class="text-sm font-medium text-gray-500">{{ __('manager.traffic_statistics.unique_pages') }}</p>
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.traffic_statistics.human') }}</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('manager.traffic_statistics.visits') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['human_visits']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('manager.traffic_statistics.page_views_count') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['human_page_views']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('manager.traffic_statistics.unique_ips') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['human_unique_ips']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 爬虫访问统计 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.traffic_statistics.bot') }}</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('manager.traffic_statistics.visits') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['bot_visits']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('manager.traffic_statistics.page_views_count') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['bot_page_views']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('manager.traffic_statistics.unique_ips') }}</span>
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['bot_unique_ips']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 热门页面 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('manager.traffic_statistics.top_pages') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.rank') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.page_path') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.visits') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.page_views_count') }}
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
                                                <span>{{ __('manager.traffic_statistics.no_data') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 访问记录列表（来自 TrafficStatisticResource，只保留列表） --}}
                <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row gap-4 md:items-end">
                        <div class="flex-1 min-w-[200px]">
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input 
                                type="text" 
                                wire="live.debounce.300ms=search"
                                placeholder="{{ __('manager.traffic_statistics.path') }} / {{ __('manager.traffic_statistics.ip') }} / {{ __('manager.traffic_statistics.referer') }}"
                            />
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <x-widgets.label>{{ __('manager.traffic_statistics.spider_source') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterSpiderSources" 
                                :options="$spiderSourceOptions"
                                :multiple="false"
                            />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.created_at') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.path') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.method') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.ip') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.is_bot') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.spider_source') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.count') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.locale') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.referer') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.traffic_statistics.user_agent') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($records as $record)
                                    <tr class="hover:bg-gray-50 transition-colors align-top">
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                            {{ $record->stat_date?->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-900 max-w-xs">
                                            <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono break-all" title="{{ $record->path }}">
                                                {{ \Illuminate\Support\Str::limit($record->path, 60) }}
                                            </code>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($record->method === 'GET') bg-green-100 text-green-800
                                                @elseif($record->method === 'POST') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $record->method }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                            {{ $record->ip }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if($record->is_bot)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    {{ __('manager.traffic_statistics.bot') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                                    {{ __('manager.traffic_statistics.human') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                            {{ $record->spider_source ? ucfirst($record->spider_source) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-gray-900">
                                            {{ number_format($record->count) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                            {{ $record->locale }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-900 max-w-xs">
                                            <span class="break-all" title="{{ $record->referer }}">
                                                {{ \Illuminate\Support\Str::limit($record->referer, 40) ?: '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-900 max-w-xs">
                                            <span class="break-all" title="{{ $record->user_agent }}">
                                                {{ \Illuminate\Support\Str::limit($record->user_agent, 40) ?: '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-10 h-10 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span>{{ __('manager.traffic_statistics.no_data') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $records->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('manager.traffic_statistics.navigation_label') }}" />
