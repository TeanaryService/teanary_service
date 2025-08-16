@props([
    'url' => request()->url(),
    'title' => config('app.name'),
    'description' => '',
    'image' => ''
])

@php
$socialShare = [
    [
        'name' => 'Facebook',
        'url' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url),
        'icon' => 'facebook.svg'
    ],
    [
        'name' => 'Twitter',
        'url' => "https://twitter.com/intent/tweet?url=" . urlencode($url) . "&text=" . urlencode($title),
        'icon' => 'twitter.svg'
    ],
    [
        'name' => 'Instagram',
        'url' => "javascript:void(0)",
        'icon' => 'instagram.svg',
        'onclick' => "navigator.clipboard.writeText('" . $url . "'); alert('链接已复制，请在 Instagram 中分享')"
    ],
    [
        'name' => 'LinkedIn',
        'url' => "https://www.linkedin.com/shareArticle?mini=true&url=" . urlencode($url) . "&title=" . urlencode($title),
        'icon' => 'linkedin.svg'
    ],
    [
        'name' => 'Pinterest',
        'url' => "https://pinterest.com/pin/create/button/?url=" . urlencode($url) . "&media=" . urlencode($image) . "&description=" . urlencode($description),
        'icon' => 'pinterest.svg'
    ],
    [
        'name' => 'WhatsApp',
        'url' => "https://wa.me/?text=" . urlencode($title . ' ' . $url),
        'icon' => 'whatsapp.svg'
    ],
];
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4']) }}>
    @foreach($socialShare as $share)
        <a href="{{ $share['url'] }}" 
           target="_blank"
           rel="noopener noreferrer"
           class="hover:text-teal-600 transition-colors duration-200"
           onclick="{{ $share['onclick'] ?? "window.open(this.href, 'share-" . strtolower($share['name']) . "','width=600,height=400'); return false;" }}"
           aria-label="Share on {{ $share['name'] }}">
            <img src="{{ asset('icons/' . $share['icon']) }}" 
                 class="w-6 h-6"
                 alt="Share on {{ $share['name'] }}">
        </a>
    @endforeach
</div>
