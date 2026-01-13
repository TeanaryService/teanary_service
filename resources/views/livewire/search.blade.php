@php
    $breadcrumbs = buildSearchBreadcrumbs();
@endphp

<div class="min-h-[70vh] mb-10">
    <div class="max-w-7xl mx-auto px-6">
        <x-breadcrumbs :items="$breadcrumbs" />

        @if ($query)
            <h1 class="text-2xl font-bold mb-8">{{ __('search.results_for', ['query' => $query]) }}</h1>

            @if ($products->isEmpty() && $articles->isEmpty())
                <div class="text-center py-12">
                    <p class="text-gray-500">{{ __('search.no_results') }}</p>
                </div>
            @else
                @if ($products->isNotEmpty())
                    <div class="mb-12">
                        <h2 class="text-xl font-semibold mb-6 flex justify-between items-center">
                            {{ __('search.products') }}
                            <a href="{{ locaRoute('product', ['search' => $query]) }}"
                                class="text-sm text-teal-600 hover:text-teal-700">
                                {{ __('search.view_all') }}
                            </a>
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach ($products as $product)
                                <x-product-item :product="$product" />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($articles->isNotEmpty())
                    <div>
                        <h2 class="text-xl font-semibold mb-6 flex justify-between items-center">
                            {{ __('search.articles') }}
                            <a href="{{ locaRoute('article.index', ['search' => $query]) }}"
                                class="text-sm text-teal-600 hover:text-teal-700">
                                {{ __('search.view_all') }}
                            </a>
                        </h2>
                        <div class="space-y-6">
                            @foreach ($articles as $article)
                                <x-article-item :article="$article" />
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('search.title') }}" description="{{ __('search.description') }}" />
@endPushOnce
