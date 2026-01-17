<x-filament-panels::page>
    <div class="space-y-6">
        {{-- 筛选器 --}}
        <x-filament::section>
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('filament.TrafficStatistics.date_range') }}
                    </label>
                    <select 
                        wire:model.live="dateRange" 
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="today">{{ __('filament.TrafficStatistics.today') }}</option>
                        <option value="yesterday">{{ __('filament.TrafficStatistics.yesterday') }}</option>
                        <option value="7days">{{ __('filament.TrafficStatistics.7days') }}</option>
                        <option value="30days">{{ __('filament.TrafficStatistics.30days') }}</option>
                        <option value="90days">{{ __('filament.TrafficStatistics.90days') }}</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('filament.TrafficStatistics.visitor_type') }}
                    </label>
                    <select 
                        wire:model.live="visitorType" 
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="all">{{ __('filament.TrafficStatistics.all') }}</option>
                        <option value="human">{{ __('filament.TrafficStatistics.human') }}</option>
                        <option value="bot">{{ __('filament.TrafficStatistics.bot') }}</option>
                    </select>
                </div>
            </div>
        </x-filament::section>

        {{-- 统计卡片 Widget --}}
        @livewire(\App\Filament\Manager\Widgets\TrafficStatsWidget::class, [
            'dateRange' => $this->dateRange,
            'visitorType' => $this->visitorType,
        ], key('traffic-stats-' . $this->dateRange . '-' . $this->visitorType))

        {{-- 访问趋势图表 --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament.TrafficStatistics.visit_trend') }}
            </x-slot>
            @livewire(\App\Filament\Manager\Widgets\TrafficChartWidget::class, [
                'filter' => $this->visitorType,
                'dateRange' => $this->dateRange,
            ], key('traffic-chart-' . $this->dateRange . '-' . $this->visitorType))
        </x-filament::section>

        {{-- 热门页面 --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament.TrafficStatistics.top_pages') }}
            </x-slot>
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('filament.TrafficStatistics.rank') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('filament.TrafficStatistics.page_path') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('filament.TrafficStatistics.visits') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('filament.TrafficStatistics.page_views_count') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->getTopPages() as $index => $page)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    #{{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded font-mono">{{ $page['path'] }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100 font-medium">
                                    {{ Number::format($page['total_visits']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100 font-medium">
                                    {{ Number::format($page['page_views']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
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
        </x-filament::section>
    </div>
</x-filament-panels::page>
