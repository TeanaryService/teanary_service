{{-- 茶文化背景组件 --}}
@props(['type' => 'default', 'intensity' => 'light'])

@php
    $intensities = [
        'light' => 'opacity-5',
        'medium' => 'opacity-10',
        'strong' => 'opacity-15'
    ];
    $intensityClass = $intensities[$intensity] ?? $intensities['light'];
@endphp

@if($type === 'tea-garden')
    {{-- 茶园背景 --}}
    <div class="absolute inset-0 {{ $intensityClass }} pointer-events-none">
        <div class="absolute top-10 left-10 w-32 h-32 bg-tea-500 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-24 h-24 bg-bamboo-500 rounded-full blur-2xl"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-ceramic-500 rounded-full blur-xl"></div>
        <div class="absolute top-1/4 right-1/3 w-20 h-20 bg-tea-400 rounded-full blur-2xl"></div>
        <div class="absolute bottom-1/4 left-1/2 w-12 h-12 bg-bamboo-400 rounded-full blur-xl"></div>
    </div>
@elseif($type === 'mountain-tea')
    {{-- 山茶背景 --}}
    <div class="absolute inset-0 {{ $intensityClass }} pointer-events-none">
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-tea-100/20 via-transparent to-bamboo-100/20"></div>
        <div class="absolute top-20 left-20 w-40 h-40 bg-tea-300 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-32 h-32 bg-bamboo-300 rounded-full blur-2xl"></div>
        <div class="absolute top-1/2 right-1/4 w-24 h-24 bg-ceramic-300 rounded-full blur-xl"></div>
    </div>
@elseif($type === 'ceremony')
    {{-- 茶道背景 --}}
    <div class="absolute inset-0 {{ $intensityClass }} pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-tr from-tea-50/30 via-transparent to-ceramic-50/30"></div>
        <div class="absolute top-1/4 left-1/4 w-28 h-28 bg-tea-400 rounded-full blur-2xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-20 h-20 bg-ceramic-400 rounded-full blur-xl"></div>
        <div class="absolute top-1/2 left-1/2 w-16 h-16 bg-bamboo-400 rounded-full blur-lg"></div>
    </div>
@else
    {{-- 默认茶文化背景 --}}
    <div class="absolute inset-0 {{ $intensityClass }} pointer-events-none">
        <div class="absolute top-10 left-10 w-32 h-32 bg-tea-500 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-24 h-24 bg-bamboo-500 rounded-full blur-2xl"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-ceramic-500 rounded-full blur-xl"></div>
    </div>
@endif
