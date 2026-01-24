@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? config('app.name') }} - {{ __('app.manager_panel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @livewireScripts

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <link rel="alternate icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}" />
    <link rel="alternate icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
</head>

<body class="bg-gray-50 font-chinese antialiased">
    <div class="min-h-screen flex">
        {{-- 侧边栏 --}}
        <x-manager.sidebar />

        {{-- 主内容区 --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- 顶部导航栏 --}}
            <x-manager.topbar />

            {{-- 页面内容 --}}
            <main class="flex-1 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
