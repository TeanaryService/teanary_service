@props([
    'for' => null,
    'required' => false,
    'class' => '',
])

@php
    $baseClasses = 'block text-base font-semibold text-gray-800 mb-2.5';
    $classes = trim($baseClasses . ' ' . $class);
@endphp

<label 
    @if($for) for="{{ $for }}" @endif
    class="{{ $classes }}"
    {{ $attributes->except(['for', 'required', 'class']) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-red-500">*</span>
    @endif
</label>
