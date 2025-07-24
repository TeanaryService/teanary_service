@php
    $breadcrumbs = [
        [
            'label' => __('article.title'),
            'url' => '',
        ],
    ];
@endphp

<div class="max-w-5xl mx-auto px-6 py-2 min-h-screen bg-white">
    <x-breadcrumbs :items="$breadcrumbs" />

    <div class="space-y-8">
        @foreach ($articles as $article)
            @php
                $translation = $article->articleTranslations->first();
                $title = $translation?->title ?? 'Untitled';
                $summary = $translation?->summary ?? '';
                $image = $article->getFirstMediaUrl('image', 'thumb'); // 建议使用 16:9 缩略图尺寸
            @endphp

            <div
                class="bg-gray-50 hover:bg-white transition duration-300 p-4 rounded-lg shadow-sm border border-gray-200 flex gap-6 items-start">
                @if ($image)
                    <div class="w-40 h-28 flex-shrink-0 overflow-hidden rounded-md">
                        <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}">
                            <img src="{{ $image }}" alt="{{ $title }}" class="object-cover w-full h-full">
                        </a>
                    </div>
                @endif

                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-900 mb-2">
                        <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}"
                            class="hover:underline hover:text-primary-600">
                            {{ $title }}
                        </a>
                    </h2>

                    <p class="text-sm text-gray-500 mb-2">
                        {{ $article->created_at->format('F j, Y') }}
                    </p>

                    <p class="text-gray-700 leading-relaxed line-clamp-3">
                        {{ $summary }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-10">
        {{ $articles->links() }}
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('article.title') }}" description="{{ __('article.description') }}"
        keywords="{{ __('article.keywords') }}" />
@endPushOnce
