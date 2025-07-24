@php
    $translation = $article->articleTranslations->first();

    $breadcrumbs = [
        [
            'label' => __('article.base_name'),
            'url' => locaRoute('article.index'),
        ],
        [
            'label' => $translation?->title,
            'url' => '',
        ],
    ];

    $cover = $article->getFirstMediaUrl('image');
@endphp

<div class="max-w-5xl mx-auto px-6 py-2 min-h-screen bg-white">
    <x-breadcrumbs :items="$breadcrumbs" />

    <article class="space-y-8">
        {{-- 封面图 --}}
        @if ($cover)
            <div class="overflow-hidden rounded-lg shadow-sm">
                <img src="{{ $cover }}" alt="{{ $translation?->title }}" class="w-full h-auto object-cover">
            </div>
        @endif

        {{-- 标题与摘要 --}}
        <header class="space-y-2">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $translation?->title }}</h1>
            <p class="text-base text-gray-500">
                {{ $article->created_at->format('F j, Y') }}
            </p>
            @if ($translation?->summary)
                <p class="text-lg text-gray-600 leading-relaxed">
                    {{ $translation->summary }}
                </p>
            @endif
        </header>

        {{-- 正文内容 --}}
        <div class="prose prose-lg max-w-none prose-gray">
            {!! $translation?->content !!}
        </div>
    </article>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ $translation?->title }}" description="{{ $translation?->summary }}" />
@endPushOnce
