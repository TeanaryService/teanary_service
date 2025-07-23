@php
    $translation = $article->articleTranslations->first();
@endphp

<div class="max-w-7xl mx-auto px-4 py-10 min-h-screen bg-white space-y-6">
    <h1 class="text-3xl font-bold">{{ $translation?->title }}</h1>
    <div class="text-gray-600">{{ $translation?->summary }}</div>
    <div class="prose max-w-full">{!! $translation?->content !!}</div>
</div>
