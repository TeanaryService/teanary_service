@php
    $breadcrumbs = [
        [
            'label' => __('article.title'),
            'url' => '',
        ],
    ];
@endphp

<div class="max-w-7xl mx-auto px-6 min-h-[70vh] bg-white">
    <x-breadcrumbs :items="$breadcrumbs" />
    <div class="flex gap gap-6">
        <div class="hidden lg:block w-1/4">
            <livewire:components.random-products :limit="2" class="grid-cols-1"/>
            <livewire:components.random-articles :limit="4" class="grid-cols-1"/>
        </div>
        <div class="w-full lg:w-3/4">
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
    <x-layouts.seo title="{{ __('article.title') }}" description="{{ __('article.description') }}"
        keywords="{{ __('article.keywords') }}" />
@endPushOnce