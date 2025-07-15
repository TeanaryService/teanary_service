<x-layouts.app>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center -mt-16 relative">
        <x-grid-bg/>
        <div class="max-w-xl px-4 text-center relative">
            {{-- 大标题 --}}
            <h1
                class="text-9xl font-black bg-gradient-to-r from-green-500 via-green-900 to-green-500 bg-clip-text text-transparent animate-pulse">
                {{ $code }}
            </h1>

            {{-- 副标题与说明 --}}
            <div class="mt-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    {{ $title }}
                </h2>
                <p class="text-gray-600">
                    {{ $subTitle }}
                </p>
            </div>

            {{-- 返回按钮 --}}
            <div class="mt-12 flex justify-center gap-4">
                <a href="{{ url()->previous() }}"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    返回上页
                </a>
                <a href="{{ route('home') }}"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    返回首页
                </a>
            </div>

            {{-- 装饰图形 --}}
            <div class="mt-12 select-none pointer-events-none opacity-75">
                <div class="relative">
                    <div class="absolute -top-16 left-1/2 transform -translate-x-1/2">
                        <svg class="w-32 h-32 text-green-100" fill="currentColor" viewBox="0 0 200 200">
                            <path
                                d="M100 0C44.8 0 0 44.8 0 100s44.8 100 100 100 100-44.8 100-100S155.2 0 100 0zm0 180c-44.2 0-80-35.8-80-80s35.8-80 80-80 80 35.8 80 80-35.8 80-80 80z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('styles')
        <style>
            @keyframes float {
                0% {
                    transform: translateY(0px);
                }

                50% {
                    transform: translateY(-20px);
                }

                100% {
                    transform: translateY(0px);
                }
            }

            .animate-float {
                animation: float 6s ease-in-out infinite;
            }
        </style>
    @endPushOnce

    @pushOnce('tdk')
        <x-layouts.tdk title="{{ $title }}" description="{{ $subTitle }}" />
    @endPushOnce
</x-layouts.app>
