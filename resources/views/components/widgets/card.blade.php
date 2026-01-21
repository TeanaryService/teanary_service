@props([
    'padding' => 'p-6', // p-4, p-6, p-8
    'shadow' => 'shadow-sm', // shadow-sm, shadow-md, shadow-lg
    'class' => '',
])

@php
    $baseClasses = 'bg-white rounded-xl border border-gray-200';
    $classes = trim("{$baseClasses} {$padding} {$shadow} {$class}");
@endphp

<div class="{{ $classes }}">
    {{ $slot }}
</div>
