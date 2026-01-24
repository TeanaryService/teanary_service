@props([
    'spacing' => 'space-y-6', // space-y-4, space-y-6, space-y-8
    'class' => '',
])

@php
    $baseClasses = $spacing;
    $classes = trim($baseClasses . ' ' . $class);
@endphp

{{-- 统一的表单容器，提供一致的间距 --}}
<div class="{{ $classes }}">
    {{ $slot }}
</div>
