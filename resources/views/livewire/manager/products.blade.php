@php
    $products = $this->products;
    $categories = $this->categories;
    $statusOptions = $this->statusOptions;
    $translationStatusOptions = $this->translationStatusOptions;
    $locale = app()->getLocale();
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.ProductResource.pluralLabel') }}</h1>
                </div>
                <a href="{{ locaRoute('manager.products.create') }}" 
                   class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                    {{ __('app.add_new') }}
                </a>
            </div>

            {{-- 筛选器 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- 搜索 --}}
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                            placeholder="{{ __('filament.product.name') }} / {{ __('filament.product.slug') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    {{-- 状态筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.product.status') }}</label>
                        <select wire:model.live="statusFilter" multiple
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 翻译状态筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.product.translation_status') }}</label>
                        <select wire:model.live="translationStatusFilter" multiple
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($translationStatusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 分类筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.product.categories') }}</label>
                        <select wire:model.live="categoryFilter"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 库存筛选 --}}
                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="lowStockFilter" 
                                class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="ml-2 text-sm text-gray-700">{{ __('filament.product.low_stock') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="outOfStockFilter" 
                                class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="ml-2 text-sm text-gray-700">{{ __('filament.product.out_of_stock') }}</span>
                        </label>
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

            {{-- 商品列表 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.product.image') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.product.name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.product.slug') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.product.variants_count') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.product.status') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.product.translation_status') }}
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
                            @forelse($products as $product)
                                @php
                                    $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                                    $productName = $translation?->name ?? $product->productTranslations->first()?->name ?? __('filament.product.unnamed');
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($product->getFirstMediaUrl('images', 'thumb'))
                                            <img src="{{ $product->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $productName }}" 
                                                class="w-16 h-16 rounded-lg object-cover">
                                        @else
                                            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $productName }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $product->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm text-gray-900">{{ $product->product_variants_count }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                \App\Enums\ProductStatusEnum::Active->value => 'bg-green-100 text-green-800',
                                                \App\Enums\ProductStatusEnum::Inactive->value => 'bg-red-100 text-red-800',
                                            ];
                                            $color = $statusColors[$product->status->value] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                            {{ $product->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                \App\Enums\TranslationStatusEnum::NotTranslated->value => 'bg-gray-100 text-gray-800',
                                                \App\Enums\TranslationStatusEnum::Pending->value => 'bg-yellow-100 text-yellow-800',
                                                \App\Enums\TranslationStatusEnum::Translated->value => 'bg-green-100 text-green-800',
                                            ];
                                            $color = $statusColors[$product->translation_status->value] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                            {{ $product->translation_status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $product->created_at->format('Y-m-d H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ locaRoute('manager.products.show', ['product' => $product->id]) }}" 
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
                @if($products->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-manager.layout>
</div>
