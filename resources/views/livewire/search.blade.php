@php
    $breadcrumbs = buildSearchBreadcrumbs();
@endphp

<div class="min-h-[60vh] mb-10">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />

        @if ($query)
            <x-widgets.page-title 
                :title="__('search.results_for', ['query' => $query])"
                class="mb-8"
            />

            @if ($products->isEmpty() && $articles->isEmpty())
                <x-widgets.empty-state 
                    icon="heroicon-o-magnifying-glass"
                    :title="__('search.no_results')"
                    :description="__('search.try_different_keywords') ?? '请尝试使用不同的关键词'"
                />
            @else
                @if ($products->isNotEmpty())
                    <div class="mb-12">
                        <x-widgets.section-title 
                            :title="__('search.products')"
                        >
                            <x-slot:actions>
                                <a href="{{ locaRoute('product', ['search' => $query]) }}"
                                    class="text-sm text-teal-600 hover:text-teal-700 font-medium">
                                    {{ __('search.view_all') }}
                                </a>
                            </x-slot:actions>
                        </x-widgets.section-title>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach ($products as $product)
                                <x-widgets.product-item :product="$product" />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($articles->isNotEmpty())
                    <div>
                        <x-widgets.section-title 
                            :title="__('search.articles')"
                        >
                            <x-slot:actions>
                                <a href="{{ locaRoute('article.index', ['search' => $query]) }}"
                                    class="text-sm text-teal-600 hover:text-teal-700 font-medium">
                                    {{ __('search.view_all') }}
                                </a>
                            </x-slot:actions>
                        </x-widgets.section-title>
                        <div class="space-y-6">
                            @foreach ($articles as $article)
                                <x-widgets.article-item :article="$article" />
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>

<x-seo-meta title="{{ __('search.title') }}" description="{{ __('search.description') }}" />
