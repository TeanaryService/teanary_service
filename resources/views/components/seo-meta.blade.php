@props([
    'title',
    'description' => null,
    'keywords' => null,
    'image' => null,
])

@pushOnce('seo')
    <x-layouts.seo 
        title="{{ $title }}" 
        description="{{ $description ?? $title }}"
        keywords="{{ $keywords ?? $title }}"
        :image="$image"
    />
@endPushOnce
