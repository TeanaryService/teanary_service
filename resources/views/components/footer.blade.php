<footer class="bg-white border-t border-gray-200 text-gray-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-sm">

            <!-- 品牌 LOGO -->
            <div>
                <a href="{{ locaRoute('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('logo.svg') }}" class="h-10 w-auto object-contain" alt="Logo">
                    <span class="font-semibold text-lg text-gray-800">{{ config('app.name') }}</span>
                </a>
                <p class="mt-4 text-gray-500">
                    {{ __('app.welcome', ['name' => config('app.name')]) }}
                </p>
            </div>

            <!-- 快速链接 -->
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">{{ __('app.quick_links') }}</h3>
                <ul class="space-y-1">
                    <li><a href="{{ locaRoute('home') }}" class="hover:text-teal-600">{{ __('app.home') }}</a></li>
                    <li><a href="{{ locaRoute('product') }}" class="hover:text-teal-600">{{ __('app.categories') }}</a>
                    </li>
                    <li><a href="{{ locaRoute('auth.login') }}" class="hover:text-teal-600">{{ __('app.login') }}</a>
                    </li>
                    {{-- <li><a href="{{ locaRoute('auth.register') }}" class="hover:text-teal-600">{{ __('app.register') }}</a></li> --}}
                    <li><a href="{{ locaRoute('article.index') }}"
                            class="hover:text-teal-600">{{ __('article.base_name') }}</a></li>
                </ul>
            </div>

            <!-- 联系方式 -->
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">{{ __('app.contact_us') }}</h3>
                <ul class="space-y-1 text-gray-500">
                    <li>📧 hello@teanary.com</li>
                    <li>📞 +86 18184839903</li>
                    <li>📍 {{ __('app.address') }}</li>
                </ul>
            </div>

            <!-- 社交媒体 -->
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">{{ __('app.follow_us') }}</h3>
                <div class="flex gap-4">
                    <a href="https://www.youtube.com/@tea-sanctuary" target="_black" class="hover:text-teal-600"
                        aria-label="Youtube">
                        <img src="/icons/youtube.svg" class="w-6 h-6">
                    </a>
                    <a target="_black" href="https://www.facebook.com/xcalderdai/" class="hover:text-teal-600"
                        aria-label="Facebook">
                        <img src="/icons/facebook.svg" class="h-6 w-6">
                    </a>
                    <a href="https://www.instagram.com/xcalderdai/" target="_black" class="hover:text-teal-600"
                        aria-label="Instagram">
                        <img src="/icons/instagram.svg" class="w-7 h-7">
                    </a>
                    <a href="https://ca.pinterest.com/calderdai/" target="_black" class="hover:text-teal-600"
                        aria-label="Pinterest">
                        <img src="/icons/pinterest.svg" class="w-7 h-7">
                    </a>
                    <a href="https://www.threads.com/@xcalderdai" target="_black" class="hover:text-teal-600"
                        aria-label="Threads">
                        <img src="/icons/threads.svg" class="w-7 h-7">
                    </a>
                    <a href="https://www.tiktok.com/@teanary" target="_black" class="hover:text-teal-600"
                        aria-label="Tiktok">
                        <img src="/icons/tiktok.svg" class="w-7 h-7">
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; {{ now()->year }} {{ config('app.name') }}. {{ __('app.rights_reserved') }}
        </div>
    </div>
</footer>
