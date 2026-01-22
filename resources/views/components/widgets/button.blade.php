@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success
    'size' => 'md', // sm, md, lg, xl
    'class' => '',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 shadow-sm hover:shadow-md';
    
    // Variant classes
    $variantClasses = match($variant) {
        'primary' => 'text-white bg-teal-600 hover:bg-teal-700 focus:ring-teal-500 active:bg-teal-800',
        'secondary' => 'text-gray-700 bg-white border-2 border-gray-300 hover:bg-gray-50 hover:border-gray-400 focus:ring-teal-500 active:bg-gray-100',
        'danger' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 active:bg-red-800',
        'danger-outline' => 'text-red-700 bg-white border-2 border-red-300 hover:bg-red-50 hover:border-red-400 focus:ring-red-500 active:bg-red-100',
        'success' => 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 active:bg-green-800',
        default => 'text-white bg-teal-600 hover:bg-teal-700 focus:ring-teal-500 active:bg-teal-800',
    };
    
    // Size classes - 统一间距系统
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-5 py-2.5 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2.5',
        'xl' => 'px-8 py-4 text-lg gap-3',
        default => 'px-5 py-2.5 text-sm gap-2',
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
