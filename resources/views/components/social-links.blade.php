@props(['class' => ''])

@php
    $socialLinks = getSocialLinks();
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 ' . $class]) }}>
    @foreach($socialLinks as $link)
        <a href="{{ $link['url'] }}" 
           target="_blank" 
           class="hover:text-teal-600"
           aria-label="{{ $link['name'] }}">
            <img src="{{ asset('icons/' . $link['icon']) }}" 
                 class="w-7 h-7"
                 alt="{{ $link['name'] }}">
        </a>
    @endforeach
</div>
