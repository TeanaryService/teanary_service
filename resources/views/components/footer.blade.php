<footer class="tea-footer text-gray-600">
    <livewire:contact-form />
    <div class="bg-white">
        <div class="max-w-7xl mx-auto px-6 md:px-8 py-12 md:py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12 text-sm">

                <!-- 品牌 LOGO -->
                <div>
                    <a href="{{ locaRoute('home') }}" class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('logo.svg') }}" class="h-10 w-auto object-contain" alt="Logo">
                        <span class="font-bold text-lg text-gray-900">{{ config('app.name') }}</span>
                    </a>
                    <p class="mt-4 text-gray-600 leading-relaxed">
                        {{ __('app.welcome', ['name' => config('app.name')]) }}
                    </p>
                </div>

                <!-- 快速链接 -->
                <div class="hidden md:block">
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
                        </ul>
                        <ul class="space-y-2.5">
                            @foreach ($categories as $category)
                                <li>
                                    <a href="{{ locaRoute('product', ['slug' => $category['slug']]) }}"
                                        class="hover:text-tea-600 transition-colors duration-200">{{ $category['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- 联系方式 -->
                <div>
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
                </div>

                <!-- 社交媒体 -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('app.follow_us') }}</h3>
                    <x-social-links class="justify-start" />
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-200 text-center text-sm text-gray-500">
                <div>&copy; {{ now()->year }} {{ config('app.name') }}. {{ __('app.rights_reserved') }}</div>
            </div>
        </div>
    </div>
</footer>
