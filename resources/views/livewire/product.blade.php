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
                <form method="GET" action="{{ locaRoute('product') }}" class="mt-8">
                    @foreach ($attributes as $attr)
                        @if(is_array($attr) && isset($attr['name']) && isset($attr['values']))
                        <div class="mb-4">
                            <div class="font-semibold text-tea-700 mb-2">{{ $attr['name'] }}</div>
                            @foreach ($attr['values'] as $val)
                                <x-widgets.checkbox 
                                    name="attributes[{{ $attr['id'] }}][]"
                                    :value="$val['id']"
                                    :checked="isset($attributeFilters[$attr['id']]) && in_array($val['id'], (array) $attributeFilters[$attr['id']])"
                                    :label="$val['name']"
                                    class="!gap-2 py-2"
                                />
                            @endforeach
                        </div>
                        @endif
                    @endforeach
                    <x-widgets.button 
                        type="submit"
                        class="w-full mt-2"
                    >
                        {{ __('home.search') }}
                    </x-widgets.button>
                </form>
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
