<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta content="ie=edge,chrome=1" http-equiv="X-UA-Compatible" />
    <meta name="google" value="notranslate">
    <meta name="renderer" content="webkit">
    <meta name="force-rendering" content="webkit">
    {{-- Start of Baidu Transcode --}}
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta http-equiv="Cache-Control" content="no-transform" />
    <meta name="MobileOptimized" content="width" />
    <meta name="HandheldFriendly" content="true" />
    {{-- End of Baidu Transcode --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @stack('tdk')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Styles -->
    @livewireStyles

    <link rel="shortcut icon" type="image/icon" href="{{ asset('/favicon.ico') }}" />
</head>

<body class="body bg-green-50">
    <div>
        <div class="fixed w-full top-0 bg-green-50 z-50">
            <div class="w-full max-w-7xl mx-auto flex justify-between h-16 items-center px-4">
                <div>
                    <a href="{{ locaRoute('home') }}"><x-layouts.logo imgClass="w-14 h-14"/></a>
                </div>
                <div class="flex gap gap-x-6 whitespace-nowrap items-center">
                    @auth
                        <x-user-menu />
                    @endauth
                    @guest
                        <a href="{{ locaRoute('filament.personal.auth.register') }}">{{ __('app.register') }}</a>
                        <a href="{{ locaRoute('filament.personal.auth.login') }}">{{ __('app.login') }}</a>
                    @endguest

                    <x-language-switch/>
                    <x-currency-switch/>
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
            <x-layouts.logo imgClass="w-20 h-20" :showText="false"/>
        </div>
    </footer>

    @livewireScripts
</body>

</html>
