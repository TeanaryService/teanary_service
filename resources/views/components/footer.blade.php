<footer class="tea-footer text-gray-600">
    <livewire:contact-form />
    <div class="bg-white">
        <div class="max-w-7xl mx-auto px-6 md:px-8 py-12 md:py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 justify-between text-sm">

                <!-- 品牌 LOGO -->
                <div class="hidden md:block w-full flex flex-col self-center">
                    <a href="{{ locaRoute('home') }}" class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('logo.svg') }}" class="h-10 w-auto object-contain" alt="Logo">
                        <span class="font-bold text-lg text-gray-900">{{ config('app.name') }}</span>
                    </a>
                    <p class="text-gray-600 leading-relaxed">
                        {{ __('app.welcome', ['name' => config('app.name')]) }}
                    </p>
                </div>

                <!-- 快速链接 -->
                <div class="hidden md:block w-full">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('app.quick_links') }}</h3>
                    <div class="grid grid-cols-2 gap-x-4">
                        <ul class="space-y-2.5">
                            <li><a href="{{ locaRoute('home') }}" class="hover:text-tea-600 transition-colors duration-200">{{ __('app.home') }}</a>
                            </li>
                            <li><a href="{{ locaRoute('auth.login') }}"
                                    class="hover:text-tea-600 transition-colors duration-200">{{ __('app.login') }}</a>
                            </li>
                            <li><a href="{{ locaRoute('product') }}"
                                    class="hover:text-tea-600 transition-colors duration-200">{{ __('app.categories') }}</a>
                            </li>
                            <li><a href="{{ locaRoute('article.index') }}"
                                    class="hover:text-tea-600 transition-colors duration-200">{{ __('home.article.base_name') }}</a></li>
                            <li><a href="{{ locaRoute('teanary.open') }}"
                                    class="hover:text-tea-600 transition-colors duration-200">{{ __('app.teanary_open_source') }}</a></li>
                        </ul>
                    </div>
                </div>

                <div class="hidden md:block w-full">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('app.quick_links') }}</h3>
                    <div class="grid grid-cols-2 gap-x-4">
                        <ul class="space-y-2.5">
                            @foreach (is_array($categories) ? array_slice($categories, 0, 5) : $categories->take(5) as $category)
                                <li>
                                    <a href="{{ locaRoute('product', ['slug' => $category['slug']]) }}"
                                        class="hover:text-tea-600 transition-colors duration-200">{{ $category['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- 联系方式 -->
                <div class="hidden md:block w-full">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('app.contact_us') }}</h3>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex gap-2 items-center">
                            <x-heroicon-o-envelope class="w-5 h-5 text-tea-600" />
                            <span>hello@teanary.com</span>
                        </li>
                        <li class="flex gap-2 items-center">
                            <x-heroicon-o-phone class="w-5 h-5 text-tea-600" />
                            <span>+86 18184839903</span>
                        </li>
                        <li class="flex gap-2 items-center">
                            <x-heroicon-o-map-pin class="w-5 h-5 text-tea-600" />
                            <span>{{ __('app.address') }}</span>
                        </li>
                    </ul>
                    <!-- 社交媒体 -->
                    <div class="mt-6">
                        <x-social-links class="justify-start" />
                    </div>
                </div>
            </div>
        </div>
        <!-- 底部版权和法律链接 -->
        <div class="mt-6 py-8 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-6 md:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-gray-500">
                    <div class="text-center md:text-left">
                        &copy; {{ now()->year }} {{ config('app.name') }}. {{ __('app.rights_reserved') }}
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-4 md:gap-6">
                        <a href="{{ locaRoute('teanary.open') }}" class="hover:text-tea-600 transition-colors duration-200">
                            {{ __('app.teanary_open_source') }}
                        </a>
                        <span class="hidden md:inline text-gray-300">|</span>
                        <a href="https://github.com/TeanaryService/teanary_srvice" target="_blank" rel="noopener noreferrer" class="hover:text-tea-600 transition-colors duration-200">
                            GitHub
                        </a>
                        <span class="hidden md:inline text-gray-300">|</span>
                        <a href="https://gitee.com/teanary/teanary_service" target="_blank" rel="noopener noreferrer" class="hover:text-tea-600 transition-colors duration-200">
                            Gitee
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
