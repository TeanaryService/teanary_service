@php
    $categories = $this->categories;
    $parentCategories = $this->parentCategories;
    $translationStatusOptions = $this->translationStatusOptions;
    $locale = app()->getLocale();
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.CategoryResource.pluralLabel') }}</h1>
                </div>
                <a href="{{ locaRoute('manager.categories.create') }}" 
                   class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                    {{ __('app.add_new') }}
                </a>
            </div>

            {{-- 筛选器 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- 搜索 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                            placeholder="{{ __('filament.category.name') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    {{-- 父分类筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.category.parent') }}</label>
                        <select wire:model.live="parentIdFilter"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            <option value="0">{{ __('filament.category.root') }}</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 翻译状态筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.category.translation_status') }}</label>
                        <select wire:model.live="translationStatusFilter" multiple
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($translationStatusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
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

            {{-- 分类列表 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.category.image') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.category.name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.category.slug') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.category.parent') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.category.products_count') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.category.translation_status') }}
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
                            @forelse($categories as $category)
                                @php
                                    $translation = $category->categoryTranslations->where('language_id', $lang?->id)->first();
                                    $categoryName = $translation?->name ?? $category->categoryTranslations->first()?->name ?? __('filament.category.unnamed');
                                    
                                    $parentTranslation = $category->category?->categoryTranslations
                                        ->where('language_id', $lang?->id)
                                        ->first();
                                    $parentName = $parentTranslation?->name 
                                        ?? $category->category?->categoryTranslations->first()?->name 
                                        ?? ($category->category?->slug ?? __('filament.category.root'));
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($category->getFirstMediaUrl('image', 'thumb'))
                                            <img src="{{ $category->getFirstMediaUrl('image', 'thumb') }}" alt="{{ $categoryName }}" 
                                                class="w-12 h-12 rounded-lg object-cover">
                                        @else
                                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $categoryName }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $category->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $category->parent_id ? $parentName : __('filament.category.root') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm text-gray-900">{{ $category->products_count }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                \App\Enums\TranslationStatusEnum::NotTranslated->value => 'bg-gray-100 text-gray-800',
                                                \App\Enums\TranslationStatusEnum::Pending->value => 'bg-yellow-100 text-yellow-800',
                                                \App\Enums\TranslationStatusEnum::Translated->value => 'bg-green-100 text-green-800',
                                            ];
                                            $color = $statusColors[$category->translation_status->value] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                            {{ $category->translation_status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $category->created_at->format('Y-m-d H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ locaRoute('manager.categories.show', ['category' => $category->id]) }}" 
                                           class="text-teal-600 hover:text-teal-900">
                                            {{ __('app.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ __('app.no_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 分页 --}}
                @if($categories->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-manager.layout>
</div>
