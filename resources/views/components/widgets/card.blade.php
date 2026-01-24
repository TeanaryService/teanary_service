@props([
    'padding' => 'p-6', // p-4, p-6, p-8
    'shadow' => 'shadow-md', // shadow-sm, shadow-md, shadow-lg
    'class' => '',
])

@php
    // 使用更明显的背景色和阴影来区分卡片
    $baseClasses = 'bg-white rounded-xl border-2 border-teal-100';
    $classes = trim("{$baseClasses} {$padding} {$shadow} {$class}");
@endphp

<div class="{{ $classes }}">
    {{ $slot }}
</div>
