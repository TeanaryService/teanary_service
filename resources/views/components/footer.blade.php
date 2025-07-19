<footer class="bg-white border-t border-gray-200 text-gray-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-sm">
            
            <!-- 品牌 LOGO -->
            <div>
                <a href="{{ locaRoute('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('logo.png') }}" class="h-10 w-auto object-contain" alt="Logo">
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
                    <li><a href="{{ locaRoute('home') }}" class="hover:text-green-600">{{ __('app.home') }}</a></li>
                    <li><a href="{{ locaRoute('product') }}" class="hover:text-green-600">{{ __('app.categories') }}</a></li>
                    <li><a href="{{ route('filament.personal.auth.login') }}" class="hover:text-green-600">{{ __('app.login') }}</a></li>
                    <li><a href="{{ route('filament.personal.auth.register') }}" class="hover:text-green-600">{{ __('app.register') }}</a></li>
                </ul>
            </div>

            <!-- 联系方式 -->
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">{{ __('app.contact_us') }}</h3>
                <ul class="space-y-1 text-gray-500">
                    <li>📧 business@kmflora.com</li>
                    <li>📞 +86 18184839903</li>
                    <li>📍 {{ __('app.address') }}</li>
                </ul>
            </div>

            <!-- 社交媒体 -->
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">{{ __('app.follow_us') }}</h3>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-green-600" aria-label="Facebook">
                        <x-heroicon-o-globe-alt class="w-6 h-6" />
                    </a>
                    <a href="#" class="hover:text-green-600" aria-label="WeChat">
                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-6 h-6" />
                    </a>
                    <a href="#" class="hover:text-green-600" aria-label="Instagram">
                        <x-heroicon-o-camera class="w-6 h-6" />
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; {{ now()->year }} {{ config('app.name') }}. {{ __('app.rights_reserved') }}
        </div>
    </div>
</footer>