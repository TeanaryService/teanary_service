@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('products', __('filament.ProductResource.label'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="products" />
            
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.ProductResource.label') }}</h1>
                    <a href="{{ locaRoute('manager.products.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('app.create') }}
                    </a>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                {{-- 筛选器 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('app.search') }}
                            </label>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="{{ __('app.search_placeholder') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('filament.product.status') }}
                            </label>
                            <select 
                                wire:model.live="filterStatus" 
                                multiple
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            >
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('filament.product.translation_status') }}
                            </label>
                            <select 
                                wire:model.live="filterTranslationStatus" 
                                multiple
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            >
                                @foreach($translationStatusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('filament.product.categories') }}
                            </label>
                            <select 
                                wire:model.live="filterCategoryId" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            >
                                <option value="">{{ __('app.all') }}</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-4 items-center">
                        <div class="flex items-center gap-2">
                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" wire:model.live="filterLowStock" class="rounded border-gray-300 text-teal-600 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <span class="ml-2">{{ __('filament.product.low_stock') }}</span>
                            </label>
                            <label class="inline-flex items-center text-sm text-gray-700 ml-4">
                                <input type="checkbox" wire:model.live="filterOutOfStock" class="rounded border-gray-300 text-teal-600 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <span class="ml-2">{{ __('filament.product.out_of_stock') }}</span>
                            </label>
                        </div>
                        <button 
                            wire:click="resetFilters"
                            class="ml-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            {{ __('app.reset') }}
                        </button>
                    </div>
                </div>

                {{-- 商品列表 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.product.name') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.product.categories') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.product.price_range') }}
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.product.total_stock') }}
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.product.variants_count') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.product.status') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('filament.product.translation_status') }}
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
                                                    {{ __('filament.ProductReviewResource.label') }}
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
                                        <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-10 h-10 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>{{ __('app.no_data') }}</span>
                                            </div>
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

