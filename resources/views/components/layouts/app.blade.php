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

    <link rel="shortcut icon" type="image/icon" href="/favicon.ico" />
</head>

<body class="body bg-green-50">
    <div>
        <div class="fixed w-full top-0 bg-green-50 z-50">
            <div class="w-full max-w-7xl mx-auto flex justify-between h-16 items-center px-4">
                <div>
                    <a href="{{ route('home') }}"><x-layouts.logo /></a>
                </div>
                <div class="flex gap gap-x-6 whitespace-nowrap items-center">
                    @auth
                        <x-user-menu />
                    @endauth
                    @guest
                        <a href="{{ route('filament.personal.auth.register') }}">注册</a>
                        <a href="{{ route('filament.personal.auth.login') }}">登录</a>
                    @endguest

                    <livewire:components.locale-currency-switcher />
                </div>
            </div>
        </div>
        <div class="h-16 w-full"></div>
    </div>

    <div class="main">
        {{ $slot }}
    </div>
    <footer class="py-4">
        {{-- <div class="block text-center md:flex text-sm text-gray-500 justify-center py-6">
            <p>&copy;昆明咩信科技有限公司</p>
            <span class="hidden md:block px-2 text-gray-400">|</span>
            <a target="_blank" href="https://beian.miit.gov.cn/">滇ICP备2025050846号-1</a>
            <span class="hidden md:block px-2 text-gray-400">|</span>
            <p class="flex justify-center items-center">
                <img class="mr-1 w-3 h-3" src="{{ url('wangan.png') }}">
                <a href="https://beian.mps.gov.cn/#/query/webSearch?code=53011102001494" rel="noreferrer"
                    target="_blank">滇公网安备53011102001494号</a>
            </p>
        </div> --}}
    </footer>

    @livewireScripts
</body>

</html>
