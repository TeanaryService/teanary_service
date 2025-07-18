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

    {{-- SEO TDK --}}
    @stack('tdk')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="shortcut icon" type="image/icon" href="{{ asset('/favicon.png') }}" />
</head>

<body class="body bg-green-50">
    <div>
        <div class="fixed w-full top-0 bg-white border-b border-gray-200 z-50">
            <div class="w-full max-w-7xl mx-auto flex justify-between h-20 items-center px-6">
                <div>
                    <a href="{{ locaRoute('home') }}"><x-layouts.logo imgClass="w-16 h-16" /></a>
                </div>

                <!-- 搜索框 -->
                <div x-data="{ open: false }" class="relative flex-1 max-w-lg mx-12">
                    <form method="GET" action="{{ locaRoute('product') }}" class="hidden sm:block w-full">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="{{ __('app.search_placeholder') }}"
                                class="w-full px-4 py-3 pl-12 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                            </button>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-x-8">
                    @auth
                        <x-user-menu />
                    @endauth
                    @guest
                        <a href="{{ route('filament.personal.auth.login') }}"
                            class="text-gray-700 hover:text-green-600 font-medium">{{ __('app.login') }}</a>
                        <a href="{{ route('filament.personal.auth.register') }}"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700">
                            {{ __('app.register') }}
                        </a>
                    @endguest

                    <div class="flex items-center gap-x-8">
                        <x-language-switch />
                        <x-currency-switch />
                    </div>
                </div>
            </div>
        </div>
        <div class="h-16 w-full"></div>
    </div>

    <div class="main">
        {{ $slot }}
    </div>
    <footer class="py-8">
        <div class="block text-center md:flex text-sm text-gray-500 justify-center py-6">
            <x-layouts.logo imgClass="w-20 h-20" :showText="false" />
        </div>
    </footer>

    @livewireScripts
</body>

</html>