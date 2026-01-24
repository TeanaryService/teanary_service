@php
    $breadcrumbs = buildProductBreadcrumbs($categoryId, $categories);
@endphp

<div class="min-h-[70vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />

        <div class="flex flex-col md:flex-row gap-8">
            {{-- 分类侧栏 --}}
            <aside class="md:w-1/4 tea-card rounded-xl p-6">
                <h2 class="text-xl font-bold text-tea-800 mb-4 tea-title">{{ __('home.browse_categories') }}</h2>
                <ul class="space-y-2">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ locaRoute('product', ['slug' => $category['slug']]) }}" wire:navigate
                                class="flex gap gap-2 items-center px-4 py-2 rounded hover:bg-tea-100 transition-colors {{ $categoryId == $category['id'] ? 'bg-tea-200 font-bold text-tea-800' : 'text-tea-700' }}">
                                <img src="{{ $category['image_url'] }}" alt="{{ $category['name'] }}"
                                    class="h-6 w-6 object-cover rounded-lg">
                                <span>{{ $category['name'] }}</span>
                            </a>
                            @if (!empty($category['children']))
                                <ul class="pl-4 mt-1 space-y-1">
                                    @foreach ($category['children'] as $child)
                                        <li>
                                            <a href="{{ locaRoute('product', ['slug' => $child['slug']]) }}" wire:navigate
                                                class="flex gap gap-2 items-center px-3 py-1 rounded hover:bg-tea-50 transition-colors {{ $categoryId == $child['id'] ? 'bg-tea-200 font-bold text-tea-800' : 'text-tea-600' }}">
                                                <img src="{{ $child['image_url'] }}" alt="{{ $child['name'] }}"
                                                    class="h-6 w-6 object-cover rounded-lg">
                                                <span>{{ $child['name'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>

                {{-- 属性筛选 --}}
                @if(!empty($filterAttributes) && is_array($filterAttributes) && count($filterAttributes) > 0)
                <div class="mt-8 pt-8 border-t border-teal-500">
                    <h2 class="text-xl font-bold text-tea-800 mb-4 tea-title">{{ __('home.attributes') }}</h2>
                    <form method="GET" action="{{ locaRoute('product') }}">
                        @foreach ($filterAttributes as $index => $attr)
                            @if(is_array($attr) && isset($attr['id']) && isset($attr['name']) && isset($attr['values']) && is_array($attr['values']) && count($attr['values']) > 0)
                            <div class="mb-5 {{ $index > 0 ? 'pt-5 border-t border-teal-500' : '' }}">
                                <div class="font-semibold text-tea-700 mb-3 text-sm uppercase tracking-wide">{{ $attr['name'] }}</div>
                                <div class="space-y-2">
                                    @foreach ($attr['values'] as $val)
                                        @if(is_array($val) && isset($val['id']) && isset($val['name']))
                                        @php
                                            $isChecked = isset($attributeFilters[$attr['id']]) && in_array($val['id'], (array) $attributeFilters[$attr['id']]);
                                        @endphp
                                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg cursor-pointer transition-all duration-200 {{ $isChecked ? 'bg-tea-100 text-tea-800 font-medium' : 'hover:bg-tea-50 text-tea-600' }}">
                                            <input 
                                                type="checkbox" 
                                                name="attributes[{{ $attr['id'] }}][]"
                                                value="{{ $val['id'] }}"
                                                {{ $isChecked ? 'checked' : '' }}
                                                class="w-4 h-4 text-tea-600 border-tea-300 rounded focus:ring-tea-500 focus:ring-2 cursor-pointer"
                                            />
                                            <span class="text-sm select-none">{{ $val['name'] }}</span>
                                        </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                        <div class="mt-6 flex gap-2">
                            <x-widgets.button 
                                type="submit"
                                class="flex-1 bg-tea-600 hover:bg-tea-700 text-white"
                            >
                                {{ __('home.search') }}
                            </x-widgets.button>
                            @if(!empty($attributeFilters))
                            <a 
                                href="{{ locaRoute('product', ['slug' => request('slug')]) }}" 
                                class="px-4 py-2 rounded-lg border border-tea-300 text-tea-700 hover:bg-tea-50 transition-colors duration-200 text-sm font-medium"
                            >
                                {{ __('app.reset') }}
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
                @endif
            </aside>

            {{-- 商品列表 --}}
            <main class="flex-1">
                <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
                    @forelse ($products as $product)
                        <x-widgets.product-item :product="$product" />
                    @empty
                        <div class="col-span-4 text-center text-gray-500 py-12">
                            {{ __('home.no_products') }}
                        </div>
                    @endforelse
                </div>
                <div class="mt-8">
                    {{ $products->withQueryString()->links() }}
                </div>
            </main>
        </div>
    </div>
</div>


<x-seo-meta title="{{ $seoTitle }}" description="{{ $seoDesc }}" keywords="{{ $seoKeywords }}" image="{{ $seoImage }}" />
