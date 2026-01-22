<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="theme-color" content="#3a9d3a">
    <meta name="HandheldFriendly" content="true">
    <meta name="MobileOptimized" content="width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <meta http-equiv="Cache-Control" content="no-transform">
    <meta name="renderer" content="webkit">
    <meta name="force-rendering" content="webkit">
    <meta name="google" value="notranslate">
    <meta name="yandex-verification" content="14a451c112fe3a18" />

    {{-- SEO --}}
    @stack('seo')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @livewireScripts

    <!-- 标准 SVG favicon（现代浏览器支持） -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />

    <!-- PNG fallback for older browsers -->
    <link rel="alternate icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}" />
    <link rel="alternate icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}" />

    <!-- Apple 设备 -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />

</head>

<body class="body bg-teal-100 font-chinese antialiased">
    <!-- 导航栏 -->
    <header class="fixed w-full top-0 bg-white/98 backdrop-blur-md z-50 border-b border-gray-200/60 shadow-sm">
        <div class="w-full max-w-7xl mx-auto flex justify-between items-center h-20 px-4 md:px-8">
            <!-- Logo -->
            <div class="hidden md:flex items-center">
                <a href="{{ locaRoute('home') }}" wire:navigate class="flex items-center gap-3 group">
                    <x-layouts.logo imgClass="max-w-12 max-h-12 md:max-w-14 md:max-h-14 transition-transform group-hover:scale-105" />
                </a>
            </div>

            <!-- 移动端 Logo -->
            <div class="block md:hidden">
                <a href="{{ locaRoute('home') }}" wire:navigate><x-layouts.logo imgClass="max-w-16 max-h-16" /></a>
            </div>

            <!-- 搜索框 -->
            <div class="flex-1 max-w-2xl mx-4 md:mx-8">
                <x-widgets.search-input />
            </div>

            <!-- 右侧操作区 -->
            <div class="flex items-center gap-3 md:gap-4">
                @livewire('components.cart-dropdown')

                @auth
                    <x-users.menu />
                @endauth
                @guest
                    <a href="{{ locaRoute('auth.login') }}" wire:navigate
                        class="hidden sm:flex items-center gap-2 px-4 py-2 text-gray-700 hover:text-tea-600 font-medium transition-colors duration-200 tea-nav-item">
                        <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" />
                        <span class="hidden md:block">{{ __('app.login') }}</span>
                    </a>
                    <a href="{{ locaRoute('auth.register') }}" wire:navigate
                        class="inline-flex items-center justify-center px-4 md:px-6 py-2 text-sm font-semibold rounded-lg text-white tea-btn-primary h-10 gap-2 shadow-md hover:shadow-lg transition-all duration-200">
                        <x-heroicon-o-plus-circle class="w-5 h-5" />
                        <span class="hidden md:block">{{ __('app.register') }}</span>
                    </a>
                @endguest

                <div class="flex items-center gap-2 md:gap-3 border-l border-gray-200 pl-3 md:pl-4">
                    <x-widgets.language-switch />
                    <x-widgets.currency-switch />
                </div>
            </div>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <div class="h-20 w-full">
        <x-widgets.flash-messages />
    </div>

    <div class="main">
        {{ $slot }}
    </div>

    <x-footer />
    @livewire('components.cookie-consent')

</body>

</html>
