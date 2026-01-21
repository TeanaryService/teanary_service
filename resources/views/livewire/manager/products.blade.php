@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('products', __('manager.products.label'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="products" />
            
            <div class="flex-1">
                <x-widgets.page-header 
                    :title="__('manager.products.label')"
                >
                    <x-slot:actions>
                        <x-widgets.button href="{{ locaRoute('manager.products.create') }}" class="inline-flex items-center gap-2">
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
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input 
                                type="text" 
                                wire="live.debounce.300ms=search"
                                placeholder="{{ __('app.search_placeholder') }}"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.products.status') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterStatus" 
                                :options="$statusOptions"
                                multiple
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.products.translation_status') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterTranslationStatus" 
                                :options="$translationStatusOptions"
                                multiple
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.products.categories') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterCategoryId" 
                                :options="[['value' => '', 'label' => __('app.all')], ...collect($categories)->map(fn($cat) => ['value' => $cat['id'], 'label' => $cat['name']])->toArray()]"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-4 items-center">
                        <div class="flex items-center gap-2">
                            <x-widgets.checkbox 
                                wire="filterLowStock"
                                :label="__('manager.products.low_stock')"
                                class="!gap-2"
                            />
                            <x-widgets.checkbox 
                                wire="filterOutOfStock"
                                :label="__('manager.products.out_of_stock')"
                                class="!gap-2 ml-4"
                            />
                        </div>
                        <x-widgets.button 
                            wire:click="resetFilters"
                            variant="secondary"
                            class="ml-auto"
                        >
                            {{ __('app.reset') }}
                        </x-widgets.button>
                    </div>
                </x-widgets.card>

                {{-- 商品列表 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.products.name') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.products.categories') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.products.price_range') }}
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.products.total_stock') }}
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.products.variants_count') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.products.status') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.products.translation_status') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($products as $product)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="font-medium">
                                                {{ $product->productTranslations->first()?->name ?? $product->slug }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <code class="bg-gray-100 rounded px-1 py-0.5">{{ $product->slug }}</code>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <span class="line-clamp-2">{{ $product->category_names_text }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                            {{ $product->price_range_text }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $product->total_stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $product->total_stock }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                            {{ $product->productVariants->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $status = $product->status;
                                                $statusColor = match($status) {
                                                    \App\Enums\ProductStatusEnum::Active => 'bg-green-100 text-green-800',
                                                    \App\Enums\ProductStatusEnum::Inactive => 'bg-red-100 text-red-800',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                {{ $status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $ts = $product->translation_status;
                                                $tsColor = match($ts) {
                                                    \App\Enums\TranslationStatusEnum::NotTranslated => 'bg-gray-100 text-gray-800',
                                                    \App\Enums\TranslationStatusEnum::Pending => 'bg-yellow-100 text-yellow-800',
                                                    \App\Enums\TranslationStatusEnum::Translated => 'bg-green-100 text-green-800',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tsColor }}">
                                                {{ $ts->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-3">
                                                <a href="{{ locaRoute('manager.products.edit', ['id' => $product->id]) }}" 
                                                   class="text-teal-600 hover:text-teal-700">
                                                    {{ __('app.edit') }}
                                                </a>
                                                <a href="{{ locaRoute('manager.products.reviews', ['productId' => $product->id]) }}" 
                                                   class="text-blue-600 hover:text-blue-700">
                                                    {{ __('manager.product_reviews.label') }}
                                                </a>
                                                <button 
                                                    wire:click="deleteProduct({{ $product->id }})"
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
                                        <td colspan="8" class="px-6 py-12">
                                            <x-widgets.empty-state 
                                                icon="heroicon-o-inbox"
                                                :title="__('app.no_data')"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('manager.products.label') }}" />