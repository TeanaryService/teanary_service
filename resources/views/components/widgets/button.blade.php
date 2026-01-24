@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success
    'size' => 'md', // sm, md, lg, xl
    'class' => '',
])

@php
    // 统一基础样式：圆角、过渡、阴影、焦点
    $baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed';
    
    // Variant classes - 统一的颜色系统
    $variantClasses = match($variant) {
        'primary' => 'text-white bg-teal-600 hover:bg-teal-700 focus:ring-teal-500 active:bg-teal-800 disabled:hover:bg-teal-600',
        'secondary' => 'text-gray-700 bg-white border-2 border-gray-300 hover:bg-gray-50 hover:border-gray-400 focus:ring-teal-500 active:bg-gray-100 disabled:hover:bg-white disabled:hover:border-gray-300',
        'danger' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 active:bg-red-800 disabled:hover:bg-red-600',
        'danger-outline' => 'text-red-700 bg-white border-2 border-red-300 hover:bg-red-50 hover:border-red-400 focus:ring-red-500 active:bg-red-100 disabled:hover:bg-white disabled:hover:border-red-300',
        'success' => 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 active:bg-green-800 disabled:hover:bg-green-600',
        default => 'text-white bg-teal-600 hover:bg-teal-700 focus:ring-teal-500 active:bg-teal-800 disabled:hover:bg-teal-600',
    };
    
    // Size classes - 统一的尺寸系统，与表单输入框高度协调
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs gap-1.5 h-8',      // 小按钮，高度32px
        'md' => 'px-4 py-2 text-sm gap-2 h-10',          // 中等按钮，高度40px（与input一致）
        'lg' => 'px-5 py-2.5 text-base gap-2.5 h-12',    // 大按钮，高度48px
        'xl' => 'px-6 py-3 text-lg gap-3 h-14',         // 超大按钮，高度56px
        default => 'px-4 py-2 text-sm gap-2 h-10',      // 默认中等尺寸
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
