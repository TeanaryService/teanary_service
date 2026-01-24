@php
    $stats = $this->stats;
    $topPages = $this->topPages;
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.TrafficStatistics.navigation_label') }}</h1>
            </div>

            {{-- 筛选器 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('filament.TrafficStatistics.date_range') }}
                        </label>
                        <select wire:model.live="dateRange" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
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
                        <select wire:model.live="visitorType" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="all">{{ __('filament.TrafficStatistics.all') }}</option>
                            <option value="human">{{ __('filament.TrafficStatistics.human') }}</option>
                            <option value="bot">{{ __('filament.TrafficStatistics.bot') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 统计卡片 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <p class="text-sm font-medium text-gray-600">{{ __('filament.TrafficStatistics.total_visits') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['total_visits']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('filament.TrafficStatistics.human_visits') }}: {{ number_format($stats['human_visits']) }} | 
                        {{ __('filament.TrafficStatistics.bot_visits') }}: {{ number_format($stats['bot_visits']) }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <p class="text-sm font-medium text-gray-600">{{ __('filament.TrafficStatistics.page_views') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['total_page_views']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('filament.TrafficStatistics.human_visits') }}: {{ number_format($stats['human_page_views']) }} | 
                        {{ __('filament.TrafficStatistics.bot_visits') }}: {{ number_format($stats['bot_page_views']) }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <p class="text-sm font-medium text-gray-600">{{ __('filament.TrafficStatistics.unique_ips') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['unique_ips']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('filament.TrafficStatistics.human_visits') }}: {{ number_format($stats['human_unique_ips']) }} | 
                        {{ __('filament.TrafficStatistics.bot_visits') }}: {{ number_format($stats['bot_unique_ips']) }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <p class="text-sm font-medium text-gray-600">{{ __('filament.TrafficStatistics.unique_pages') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['unique_pages']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('filament.TrafficStatistics.pages_visited') }}</p>
                </div>
            </div>

            {{-- 热门页面 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.TrafficStatistics.top_pages') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('filament.TrafficStatistics.rank') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('filament.TrafficStatistics.page_path') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('filament.TrafficStatistics.visits') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('filament.TrafficStatistics.page_views_count') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($topPages as $index => $page)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $index + 1 }}</td>
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
                                        {{ __('filament.TrafficStatistics.no_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-manager.layout>
</div>
