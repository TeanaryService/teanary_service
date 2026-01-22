@props([
    'article',
    'size' => 'large' // large or small
])

@php
    $articleData = getArticleDisplayData($article);
    $title = $articleData['title'];
    $summary = $articleData['summary'];
    $image = $articleData['image'];
@endphp

@if($size === 'large')
    <div class="bg-gray-50 hover:bg-white transition duration-300 p-4 rounded-lg shadow-sm border border-gray-200 flex gap-6 items-start">
        @if ($image)
            <div class="w-40 h-28 flex-shrink-0 overflow-hidden rounded-md">
                <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}" wire:navigate.hover>
                    <img src="{{ $image }}" 
                         alt="{{ $title }}" 
                         class="object-cover w-full h-full">
                </a>
            </div>
        @endif

        <div class="flex-1">
            <h2 class="text-xl md:text-2xl font-semibold text-gray-900 mb-2 line-clamp-2">
                <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}" wire:navigate.hover class="hover:underline hover:text-teal-700">
                    {{ $title }}
                </a>
            </h2>
            <p class="text-sm text-gray-500 mb-2">
                {{ $article->created_at->format('F j, Y') }}
            </p>
            <p class="text-gray-700 leading-relaxed line-clamp-1">
                {{ $summary }}
            </p>
        </div>
    </div>
@else
    <div class="bg-gray-50 hover:bg-white transition duration-300 p-3 rounded-lg shadow-sm border border-gray-200 flex gap-4 items-start">
        @if ($image)
            <div class="w-24 h-16 flex-shrink-0 overflow-hidden rounded-md">
                <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}" wire:navigate.hover>
                    <img src="{{ $image }}" 
                         alt="{{ $title }}" 
                         class="object-cover w-full h-full">
                </a>
            </div>
        @endif

        <div class="flex-1">
            <h4 class="text-base font-medium text-gray-900 line-clamp-2">
                <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}" wire:navigate.hover class="hover:underline hover:text-teal-700">
                    {{ $title }}
                </a>
            </h4>
            <p class="text-xs text-gray-500 mt-1">
                {{ $article->created_at->format('Y-m-d') }}
            </p>
        </div>
    </div>
@endif
