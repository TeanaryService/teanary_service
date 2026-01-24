@php
    $breadcrumbs = buildArticleListBreadcrumbs();
@endphp

<div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8 min-h-[70vh]">
    <x-widgets.breadcrumbs :items="$breadcrumbs" />
    <div class="flex gap gap-6">
        <div class="hidden lg:block w-1/4">
            <livewire:components.random-products :limit="2" class="grid-cols-1"/>
            <livewire:components.random-articles :limit="4" class="grid-cols-1"/>
        </div>
        <div class="w-full lg:w-3/4">
            <div class="space-y-8">
                @foreach ($articles as $article)
                    <x-widgets.article-item :article="$article" size="large" />
                @endforeach
            </div>

            <x-widgets.pagination-wrapper>
                {{ $articles->links() }}
            </x-widgets.pagination-wrapper>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('home.article.title') }}" description="{{ __('home.article.description') }}" keywords="{{ __('home.article.keywords') }}" />