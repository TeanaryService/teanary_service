@php
    $translation = $article->articleTranslations->first();
    $breadcrumbs = buildArticleDetailBreadcrumbs($article);
    $cover = $article->getFirstMediaUrl('image');
@endphp

<div class="max-w-7xl mx-auto px-6 min-h-[70vh] mb-10">
    <x-breadcrumbs :items="$breadcrumbs" />
    <div class="flex gap gap-6">
        <div class="hidden lg:block w-1/4">
            <livewire:components.random-products :limit="2" class="grid-cols-1" />
            <livewire:components.random-articles :limit="4" class="grid-cols-1" />
        </div>
        <article class="space-y-8 bg-gray-50 rounded-xl p-6 w-full lg:w-3/4">
            {{-- 封面图 --}}
            @if ($cover)
                <div class="overflow-hidden rounded-lg shadow-sm">
                    <img src="{{ $cover }}" alt="{{ $translation?->title }}"
                        class="w-full h-auto max-h-96 object-cover">
                </div>
            @endif

            {{-- 标题与摘要 --}}
            <header class="space-y-2">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $translation?->title }}</h1>
                <p class="text-base text-gray-500">
                    {{ $article->created_at->format('F j, Y') }}
                </p>
                <div class="py-6">
                    <x-share-buttons title="{{ $translation?->title }}" description="{{ $translation?->summary }}"
                        image="{{ $cover }}" />
                </div>
                
                <a href="{{ locaRoute('product') }}">
                    <x-promotion-list class="pb-6" />
                </a>

                @if ($translation?->summary)
                    <p class="text-lg text-gray-600 leading-relaxed">
                        {{ $translation->summary }}
                    </p>
                @endif
            </header>

            {{-- 正文内容 --}}
            <div class="prose max-w-none prose-gray">
                {!! $translation?->content !!}
            </div>
        </article>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{!! $translation?->title !!}" description="{{ $translation?->summary }}"
        image="{{ $cover }}" />
@endPushOnce
