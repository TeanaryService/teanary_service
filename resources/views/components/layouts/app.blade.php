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
    <meta name="theme-color" content="#16a34a">
    <meta name="HandheldFriendly" content="true">
    <meta name="MobileOptimized" content="width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <meta http-equiv="Cache-Control" content="no-transform">
    <meta name="renderer" content="webkit">
    <meta name="force-rendering" content="webkit">
    <meta name="google" value="notranslate">

    {{-- SEO --}}
    @stack('seo')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @livewireScripts
    <link rel="shortcut icon" type="image/svg" href="{{ asset('/favicon.svg') }}" />
</head>

<body class="body bg-white">
    <div class="fixed w-full top-0 bg-white border-b border-gray-200 z-50">
        <div class="w-full max-w-7xl mx-auto flex justify-between h-20 items-center px-1 md:px-6">
            <div class="hidden md:block">
                <a href="{{ locaRoute('home') }}"><x-layouts.logo
                        imgClass="max-w-12 max-h-12 md:max-w-14 md:max-h-14" /></a>
            </div>

            <!-- 搜索框 -->
            <x-search-input />

            <div class="block md:hidden">
                <a href="{{ locaRoute('home') }}"><x-layouts.logo imgClass="max-w-16 max-h-16" /></a>
            </div>

            <div class="flex items-center gap-x-4 h-10">
                @livewire('components.cart-dropdown')

                @auth
                    <x-user-menu />
                @endauth
                @guest
                    <a href="{{ locaRoute('auth.login') }}"
                        class="text-teal-600 hover:text-teal-800 font-medium flex items-center gap gap-x-2">
                        <x-heroicon-o-arrow-left-on-rectangle class="w-6 h-6" />
                        <span class="hidden md:block">
                            {{ __('app.login') }}
                        </span>
                    </a>
                    <a href="{{ locaRoute('auth.register') }}"
                        class="inline-flex items-center justify-center px-2 md:px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-teal-600 hover:bg-teal-700 h-10 gap gap-x-2">
                        <x-heroicon-o-plus-circle class="w-6 h-6" />
                        <span class="hidden md:block">{{ __('app.register') }}</span>
                    </a>
                @endguest

                <div class="flex items-center gap-x-4 h-10">
                    <x-language-switch />
                    <x-currency-switch />
                </div>
            </div>
        </div>
    </div>
    <div class="h-20 w-full">
        <x-flash-messages />
    </div>

    <div class="main">
        {{ $slot }}
    </div>

    <x-footer />
    @livewire('components.cookie-consent')

</body>

</html>
