@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success
    'size' => 'md', // sm, md, lg
    'class' => '',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    // Variant classes
    $variantClasses = match($variant) {
        'primary' => 'text-white bg-teal-600 hover:bg-teal-700 focus:ring-teal-500',
        'secondary' => 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-teal-500',
        'danger' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'danger-outline' => 'text-red-700 bg-white border border-red-300 hover:bg-red-50 focus:ring-red-500',
        'success' => 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500',
        default => 'text-white bg-teal-600 hover:bg-teal-700 focus:ring-teal-500',
    };
    
    // Size classes
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2 text-sm',
    };
    
    $classes = trim($baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses . ' ' . $class);
    $isLink = $attributes->has('href');
@endphp

@if($isLink)
    <a 
        class="{{ $classes }}"
        {{ $attributes->except(['variant', 'size', 'class']) }}
    >
        {{ $slot }}
    </a>
@else
    <button 
        type="{{ $type }}"
        class="{{ $classes }}"
        {{ $attributes->except(['type', 'variant', 'size', 'class']) }}
    >
        {{ $slot }}
    </button>
@endif
