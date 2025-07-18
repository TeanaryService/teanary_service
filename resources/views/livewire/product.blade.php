<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row gap-8">
            {{-- 分类侧栏 --}}
            <aside class="md:w-1/4">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('home.browse_categories') }}</h2>
                <ul class="space-y-2">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ locaRoute('product', ['category_id' => $category['id']]) }}"
                                class="block px-4 py-2 rounded hover:bg-green-100 {{ $categoryId == $category['id'] ? 'bg-green-200 font-bold' : 'text-gray-700' }}">
                                {{ $category['name'] }}
                            </a>
                            @if (!empty($category['children']))
                                <ul class="pl-4 mt-1 space-y-1">
                                    @foreach ($category['children'] as $child)
                                        <li>
                                            <a href="{{ locaRoute('product', ['category_id' => $child['id']]) }}"
                                                class="block px-3 py-1 rounded hover:bg-green-50 {{ $categoryId == $child['id'] ? 'bg-green-200 font-bold' : 'text-gray-600' }}">
                                                {{ $child['name'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </aside>

            {{-- 商品列表 --}}
            <main class="flex-1">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
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
