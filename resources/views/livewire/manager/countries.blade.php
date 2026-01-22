@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('countries', __('manager.countries.label'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="countries" />
            
            <div class="flex-1">
                <x-widgets.page-header 
                    :title="__('manager.countries.label')"
                >
                    <x-slot:actions>
                        <x-widgets.button href="{{ locaRoute('manager.countries.create') }}" wire:navigate class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('app.create') }}
                        </x-widgets.button>
                    </x-slot:actions>
                </x-widgets.page-header>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                {{-- 筛选器 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input 
                                type="text" 
                                wire="live.debounce.300ms=search"
                                placeholder="{{ __('app.search_placeholder') }}"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.country.active') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterActive" 
                                :options="[
                                    ['value' => '', 'label' => __('app.all')],
                                    ['value' => '1', 'label' => __('manager.country.active')],
                                    ['value' => '0', 'label' => __('manager.country.inactive')]
                                ]"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.country.translation_status') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterTranslationStatus" 
                                :options="$translationStatusOptions"
                                :multiple="false"
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

                {{-- 国家列表 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.country.name') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.country.iso_code_2') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.country.iso_code_3') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.country.zones_count') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.country.postcode_required') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.country.active') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.country.translation_status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($countries as $country)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $this->getCountryName($country, $lang) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($country->iso_code_2)
                                                <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">{{ $country->iso_code_2 }}</code>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($country->iso_code_3)
                                                <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">{{ $country->iso_code_3 }}</code>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                            {{ $country->zones->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($country->postcode_required)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    是
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">否</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($country->active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ __('manager.country.active') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ __('manager.country.inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $status = $country->translation_status;
                                                $colorClass = match($status) {
                                                    \App\Enums\TranslationStatusEnum::NotTranslated => 'bg-gray-100 text-gray-800',
                                                    \App\Enums\TranslationStatusEnum::Pending => 'bg-yellow-100 text-yellow-800',
                                                    \App\Enums\TranslationStatusEnum::Translated => 'bg-green-100 text-green-800',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                                {{ $status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ locaRoute('manager.countries.edit', ['id' => $country->id]) }}" wire:navigate 
                                                   class="text-teal-600 hover:text-teal-700">
                                                    {{ __('app.edit') }}
                                                </a>
                                                <button 
                                                    wire:click="deleteCountry({{ $country->id }})"
                                                    wire:confirm="{{ __('app.confirm_delete') }}"
                                                    class="text-red-600 hover:text-red-700"
                                                >
                                                    {{ __('app.delete') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-10 h-10 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                        {{ $countries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('manager.countries.label') }}" />
