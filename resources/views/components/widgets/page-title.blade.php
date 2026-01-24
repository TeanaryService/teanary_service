@props([
    'title' => null,
    'subtitle' => null,
    'size' => 'lg', // sm, md, lg, xl
    'class' => '',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'text-2xl',
        'md' => 'text-3xl',
        'lg' => 'text-3xl md:text-4xl',
        'xl' => 'text-4xl md:text-5xl',
        default => 'text-3xl md:text-4xl',
    };
@endphp

<div class="{{ $class }}">
    @if($title)
        <h1 class="{{ $sizeClasses }} font-bold text-gray-900 mb-2">
            {{ $title }}
        </h1>
    @endif
    @if($subtitle)
        <p class="text-base md:text-lg text-gray-600 mt-2">
            {{ $subtitle }}
        </p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
