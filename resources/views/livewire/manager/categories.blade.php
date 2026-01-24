@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('categories', __('manager.categories.label'));
@endphp

<div class="min-h-[70vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="categories" />
            
            <div class="flex-1">
                <x-widgets.page-header 
                    :title="__('manager.categories.label')"
                >
                    <x-slot:actions>
                        <x-widgets.button href="{{ locaRoute('manager.categories.create') }}" wire:navigate class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('app.create') }}
                        </x-widgets.button>
                    </x-slot:actions>
                </x-widgets.page-header>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-100 p-4">
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
                            <x-widgets.label>{{ __('manager.category.parent') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterParentId" 
                                :options="[['value' => '', 'label' => __('app.all')], ['value' => '0', 'label' => __('manager.category.root')], ...collect($parentCategories)->map(fn($parent) => ['value' => $parent->id, 'label' => $this->getCategoryName($parent, $lang)])->toArray()]"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.category.translation_status') }}</x-widgets.label>
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

                {{-- 批量操作栏 --}}
                @if($this->hasSelectedItems())
                    <x-manager.batch-actions 
                        hasTranslationStatus="true"
                        :translationStatusOptions="$translationStatusOptions"
                        deleteMethod="batchDeleteCategories"
                        translationStatusMethod="batchSetCategoryTranslationStatus"
                    />
                @endif

                {{-- 分类列表 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                        <x-widgets.checkbox 
                                            standalone
                                            wireClick="toggleSelectAll"
                                            :checked="$selectAll"
                                        />
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.category.image') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.category.name') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.category.parent') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.category.slug') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.category.products_count') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.category.translation_status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($categories as $category)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-widgets.checkbox 
                                                standalone
                                                wireClick="toggleSelect({{ $category->id }})"
                                                :checked="in_array($category->id, $selectedItems)"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($category->hasMedia('image'))
                                                <img src="{{ $category->getFirstMediaUrl('image', 'thumb') }}" 
                                                     alt="{{ $this->getCategoryName($category, $lang) }}"
                                                     class="w-16 h-16 object-cover rounded-lg">
                                            @else
                                                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="font-medium">{{ $this->getCategoryName($category, $lang) }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $this->getParentName($category->category, $lang) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">{{ $category->slug }}</code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                            {{ $category->products->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $status = $category->translation_status;
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
                                                <a href="{{ locaRoute('manager.categories.edit', ['id' => $category->id]) }}" wire:navigate 
                                                   class="text-teal-600 hover:text-teal-700">
                                                    {{ __('app.edit') }}
                                                </a>
                                                <button 
                                                    wire:click="deleteCategory({{ $category->id }})"
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
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
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('manager.categories.label') }}" />
