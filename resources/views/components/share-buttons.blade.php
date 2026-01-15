@props([
    'url' => request()->url(),
    'title' => config('app.name'),
    'description' => '',
    'image' => ''
])

@php
    $socialShare = getShareButtons($url, $title, $description, $image);
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4']) }}>
    @foreach($socialShare as $share)
        @php
            $onclick = $share['onclick'] ?? "window.open(this.href, 'share-" . strtolower($share['name']) . "','width=600,height=400'); return false;";
        @endphp
        <a href="{{ $share['url'] }}" 
           target="_blank"
           rel="noopener noreferrer"
           class="hover:text-teal-600 transition-colors duration-200"
           onclick="{{ $onclick }}"
           aria-label="Share on {{ $share['name'] }}">
            <img src="{{ asset('icons/' . $share['icon']) }}" 
                 class="w-6 h-6"
                 alt="Share on {{ $share['name'] }}">
        </a>
    @endforeach
</div>
