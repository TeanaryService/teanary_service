<footer class="tea-footer text-tea-700">
    <livewire:contact-form />
    <div class="bg-gradient-to-br from-tea-100 to-bamboo-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-sm">

                <!-- 品牌 LOGO -->
                <div>
                    <a href="{{ locaRoute('home') }}" class="flex items-center gap-2">
                        <img src="{{ asset('logo.svg') }}" class="h-10 w-auto object-contain" alt="Logo">
                        <span class="font-semibold text-lg text-tea-800">{{ config('app.name') }}</span>
                    </a>
                    <p class="mt-4 text-tea-600">
                        {{ __('app.welcome', ['name' => config('app.name')]) }}
                    </p>
                </div>

                <!-- 快速链接 -->
                <div class="hidden md:block">
                    <h3 class="font-semibold text-tea-800 mb-2">{{ __('app.quick_links') }}</h3>
                    <div class="grid grid-cols-2  space-x-2">
                        <ul class="space-y-1">
                            <li><a href="{{ locaRoute('home') }}" class="hover:text-tea-600 transition-colors">{{ __('app.home') }}</a>
                            </li>
                            <li><a href="{{ locaRoute('auth.login') }}"
                                    class="hover:text-tea-600 transition-colors">{{ __('app.login') }}</a>
                            </li>
                            <li><a href="{{ locaRoute('product') }}"
                                    class="hover:text-tea-600 transition-colors">{{ __('app.categories') }}</a>
                            </li>
                            <li><a href="{{ locaRoute('article.index') }}"
                                    class="hover:text-tea-600 transition-colors">{{ __('article.base_name') }}</a></li>
                        </ul>
                        <ul class="space-y-1">
                            @foreach ($categories as $category)
                                <li>
                                    <a href="{{ locaRoute('product', ['slug' => $category['slug']]) }}"
                                        class="hover:text-tea-600 transition-colors">{{ $category['name'] }}</a>
                                </li>
                            @endforeach

                        </ul>
                    </div>
                </div>

                <!-- 联系方式 -->
                <div>
                    <h3 class="font-semibold text-tea-800 mb-2">{{ __('app.contact_us') }}</h3>
                    <ul class="space-y-1 text-tea-600">
                        <li class="flex gap gap-1 items-center">
                            <x-heroicon-o-envelope class="w-4 h-4" />
                            <span>hello@teanary.com</span>
                        </li>
                        <li class="flex gap gap-1 items-center">
                            <x-heroicon-o-phone class="w-4 h-4" />
                            <span>+86 18184839903</span>
                        </li>
                        <li class="flex gap gap-1 items-center">
                            <x-heroicon-o-map-pin class="w-4 h-4" />
                            <span>{{ __('app.address') }}</span>
                        </li>
                    </ul>
                </div>

                <!-- 社交媒体 -->
                <div>
                    <h3 class="font-semibold text-tea-800 mb-2">{{ __('app.follow_us') }}</h3>
                    <x-social-links class="justify-start" />
                </div>
            </div>

            <div class="mt-6 text-center text-xs text-tea-500">
                <div>&copy; {{ now()->year }} {{ config('app.name') }}. {{ __('app.rights_reserved') }}</div>
            </div>
        </div>
    </div>
</footer>
