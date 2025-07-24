@php
    // 构建面包屑
    $breadcrumbs = [
        [
            'label' => __('app.categories'),
            'url' => locaRoute('product'),
        ]
    ];
    if ($categoryId && !empty($categories)) {
        $category = null;
        $parent = null;
        foreach ($categories as $cat) {
            if ($cat['id'] == $categoryId) {
                $category = $cat;
                break;
            }
            foreach ($cat['children'] ?? [] as $child) {
                if ($child['id'] == $categoryId) {
                    $parent = $cat;
                    $category = $child;
                    break 2;
                }
            }
        }
        if ($parent) {
            $breadcrumbs[] = [
                'label' => $parent['name'],
                'url' => locaRoute('product', ['category_id' => $parent['id']]),
            ];
        }
        if ($category) {
            $breadcrumbs[] = [
                'label' => $category['name'],
                'url' => '',
            ];
        }
    }
@endphp

<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-6">
        <x-breadcrumbs :items="$breadcrumbs" />

        <div class="flex flex-col md:flex-row gap-8">
            {{-- 分类侧栏 --}}
            <aside class="md:w-1/4">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('home.browse_categories') }}</h2>
                <ul class="space-y-2">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ locaRoute('product', ['category_id' => $category['id']]) }}"
                                class="block px-4 py-2 rounded hover:bg-teal-100 {{ $categoryId == $category['id'] ? 'bg-teal-200 font-bold' : 'text-gray-700' }}">
                                {{ $category['name'] }}
                            </a>
                            @if (!empty($category['children']))
                                <ul class="pl-4 mt-1 space-y-1">
                                    @foreach ($category['children'] as $child)
                                        <li>
                                            <a href="{{ locaRoute('product', ['category_id' => $child['id']]) }}"
                                                class="block px-3 py-1 rounded hover:bg-teal-50 {{ $categoryId == $child['id'] ? 'bg-teal-200 font-bold' : 'text-gray-600' }}">
                                                {{ $child['name'] }}
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
                        <div class="mb-4">
                            <div class="font-semibold text-gray-700 mb-2">{{ $attr['name'] }}</div>
                            @foreach ($attr['values'] as $val)
                                <label class="flex items-center mb-1 cursor-pointer">
                                    <input type="checkbox" name="attributes[{{ $attr['id'] }}][]"
                                        value="{{ $val['id'] }}" @if (isset($attributeFilters[$attr['id']]) && in_array($val['id'], (array) $attributeFilters[$attr['id']])) checked @endif
                                        class="mr-2 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                    <span class="text-gray-600">{{ $val['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                    <button type="submit"
                        class="w-full px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 font-semibold mt-2">
                        {{ __('home.search') }}
                    </button>
                </form>
            </aside>

            {{-- 商品列表 --}}
            <main class="flex-1">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-10">
                    @forelse ($products as $product)
                        <x-product-item :product="$product" />
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

@php
    // SEO相关
    $seoTitle = '';
    $seoDesc = '';
    $seoImage = asset('logo.png');
    $seoKeywords = '';
    if ($categoryId && !empty($categories)) {
        $locale = session('lang');
        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
        $category = collect($categories)
            ->flatMap(function ($cat) {
                return array_merge([$cat], $cat['children']->toArray() ?? []);
            })
            ->firstWhere('id', $categoryId);
        if ($category) {
            $seoTitle = $category['name'];
            $seoDesc = $category['name'];
            $seoImage = $category['image_url'] ?? asset('logo.png');
        }
    } else {
        $seoTitle = __('home.product_list_seo_title');
        $seoDesc = __('home.product_list_seo_desc');
        $seoImage = asset('logo.png');
    }
    // 筛选条件加到keywords
    if (!empty($attributeFilters) && !empty($attributes)) {
        $filterNames = [];
        foreach ($attributeFilters as $attrId => $valueIds) {
            $attr = collect($attributes)->firstWhere('id', $attrId);
            if ($attr && !empty($valueIds)) {
                foreach ((array) $valueIds as $vid) {
                    $val = collect($attr['values'])->firstWhere('id', $vid);
                    if ($val) {
                        $filterNames[] = $attr['name'] . ':' . $val['name'];
                    }
                }
            }
        }
        if ($filterNames) {
            $strFilterName = implode(',', $filterNames);
            $seoKeywords .= $strFilterName;

            $seoTitle = $strFilterName . $seoTitle;
            $seoDesc .= $strFilterName;
        }
    }
@endphp

@pushOnce('seo')
    <x-layouts.seo title="{{ $seoTitle }}" description="{{ $seoDesc }}" image="{{ $seoImage }}"
        keywords="{{ $seoKeywords }}" />
@endPushOnce
