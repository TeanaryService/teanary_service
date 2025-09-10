{{-- 茶文化装饰组件 --}}
@props(['type' => 'default', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'w-8 h-8',
        'md' => 'w-12 h-12',
        'lg' => 'w-16 h-16',
        'xl' => 'w-20 h-20'
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($type === 'leaf')
    {{-- 茶叶装饰 --}}
    <div class="tea-decoration-leaf {{ $sizeClass }} opacity-20">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-tea-500">
            <path d="M12 2C8 2 4 6 4 10C4 14 8 18 12 18C16 18 20 14 20 10C20 6 16 2 12 2Z" fill="currentColor" opacity="0.3"/>
            <path d="M12 4C9 4 6 7 6 10C6 13 9 16 12 16C15 16 18 13 18 10C18 7 15 4 12 4Z" fill="currentColor" opacity="0.6"/>
            <path d="M12 6C10 6 8 8 8 10C8 12 10 14 12 14C14 14 16 12 16 10C16 8 14 6 12 6Z" fill="currentColor"/>
        </svg>
    </div>
@elseif($type === 'bamboo')
    {{-- 竹子装饰 --}}
    <div class="tea-decoration-bamboo {{ $sizeClass }} opacity-20">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-bamboo-500">
            <rect x="10" y="2" width="4" height="20" fill="currentColor" opacity="0.3"/>
            <rect x="9" y="4" width="6" height="2" fill="currentColor" opacity="0.6"/>
            <rect x="9" y="8" width="6" height="2" fill="currentColor" opacity="0.6"/>
            <rect x="9" y="12" width="6" height="2" fill="currentColor" opacity="0.6"/>
            <rect x="9" y="16" width="6" height="2" fill="currentColor" opacity="0.6"/>
        </svg>
    </div>
@elseif($type === 'ceramic')
    {{-- 陶瓷装饰 --}}
    <div class="tea-decoration-ceramic {{ $sizeClass }} opacity-20">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-ceramic-500">
            <ellipse cx="12" cy="8" rx="8" ry="4" fill="currentColor" opacity="0.3"/>
            <ellipse cx="12" cy="12" rx="8" ry="4" fill="currentColor" opacity="0.6"/>
            <ellipse cx="12" cy="16" rx="8" ry="4" fill="currentColor"/>
        </svg>
    </div>
@elseif($type === 'wave')
    {{-- 水波装饰 --}}
    <div class="tea-decoration-wave {{ $sizeClass }} opacity-20">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-tea-500">
            <path d="M2 12C2 8 6 4 12 4C18 4 22 8 22 12C22 16 18 20 12 20C6 20 2 16 2 12Z" fill="currentColor" opacity="0.1"/>
            <path d="M4 12C4 9 7 6 12 6C17 6 20 9 20 12C20 15 17 18 12 18C7 18 4 15 4 12Z" fill="currentColor" opacity="0.3"/>
            <path d="M6 12C6 10 8 8 12 8C16 8 18 10 18 12C18 14 16 16 12 16C8 16 6 14 6 12Z" fill="currentColor" opacity="0.6"/>
        </svg>
    </div>
@else
    {{-- 默认装饰 --}}
    <div class="tea-decoration-default {{ $sizeClass }} opacity-20">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-tea-500">
            <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.1"/>
            <circle cx="12" cy="12" r="6" fill="currentColor" opacity="0.3"/>
            <circle cx="12" cy="12" r="3" fill="currentColor" opacity="0.6"/>
        </svg>
    </div>
@endif

<style>
.tea-decoration-leaf {
    animation: teaFloat 4s ease-in-out infinite;
}

.tea-decoration-bamboo {
    animation: teaFloat 3s ease-in-out infinite reverse;
}

.tea-decoration-ceramic {
    animation: teaFloat 5s ease-in-out infinite;
}

.tea-decoration-wave {
    animation: teaFloat 2s ease-in-out infinite;
}

.tea-decoration-default {
    animation: teaFloat 3.5s ease-in-out infinite;
}
</style>
