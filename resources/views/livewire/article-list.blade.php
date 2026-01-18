@php
    $breadcrumbs = buildArticleListBreadcrumbs();
@endphp

<div class="max-w-7xl mx-auto px-6 md:px-8 min-h-[70vh] bg-white">
    <x-breadcrumbs :items="$breadcrumbs" />
    <div class="flex gap gap-6">
        <div class="hidden lg:block w-1/4">
            <livewire:components.random-products :limit="2" class="grid-cols-1"/>
            <livewire:components.random-articles :limit="4" class="grid-cols-1"/>
        </div>
        <div class="w-full lg:w-3/4">
            <div class="space-y-8">
                @foreach ($articles as $article)
                    <x-article-item :article="$article" size="large" />
                @endforeach
            </div>

            <div class="my-10">
                {{ $articles->links() }}
            </div>
        </div>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('home.article.title') }}" description="{{ __('home.article.description') }}"
        keywords="{{ __('home.article.keywords') }}" />
@endPushOnce