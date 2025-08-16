@props(['class' => ''])

@php
$socialLinks = [
    [
        'name' => 'Youtube',
        'url' => 'https://www.youtube.com/@tea-sanctuary',
        'icon' => 'youtube.svg'
    ],
    [
        'name' => 'Facebook',
        'url' => 'https://www.facebook.com/xcalderdai/',
        'icon' => 'facebook.svg'
    ],
    [
        'name' => 'Instagram',
        'url' => 'https://www.instagram.com/xcalderdai/',
        'icon' => 'instagram.svg'
    ],
    [
        'name' => 'Pinterest',
        'url' => 'https://ca.pinterest.com/calderdai/',
        'icon' => 'pinterest.svg'
    ],
    [
        'name' => 'Threads',
        'url' => 'https://www.threads.com/@xcalderdai',
        'icon' => 'threads.svg'
    ],
    [
        'name' => 'Tiktok',
        'url' => 'https://www.tiktok.com/@teanary',
        'icon' => 'tiktok.svg'
    ]
];
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
