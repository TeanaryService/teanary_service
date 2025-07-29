@php
    $breadcrumbs = [
        [
            'label' => __('article.title'),
            'url' => '',
        ],
    ];
@endphp

<div class="max-w-5xl mx-auto px-6 min-h-[70vh] bg-white">
    <x-breadcrumbs :items="$breadcrumbs" />

    <!-- 搜索框 -->
    <div class="mb-6 flex gap-4">
        <div class="flex-1 relative">
            <input type="text" wire:model.debounce.300ms="search" wire:keydown.enter="$refresh"
                placeholder="{{ __('app.article_search_placeholder') }}"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring focus:border-teal-300">
            @if ($search)
                <button wire:click="clearSearch"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-circle class="w-5 h-5" />
                </button>
            @endif
        </div>
        <button wire:click="$refresh"
            class="px-6 py-2 bg-teal-700 text-white rounded-md hover:bg-teal-700 transition-colors">
            搜索
        </button>
    </div>

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
                            class="hover:underline hover:text-teal-700">
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
