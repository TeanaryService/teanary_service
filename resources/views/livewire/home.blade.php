<div class="bg-gray-50">
    {{-- Hero Banner --}}
    <section class="w-full bg-teal-600 bg-gradient-to-br from-teal-600 via-gray-50 to-tea-50/30 py-20 md:py-28 relative overflow-hidden">
        <!-- 茶文化装饰背景 -->
        <x-tea-background type="tea-garden" intensity="light" />
        
        <!-- 茶文化装饰元素 -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-20 left-20">
                <x-tea-decoration type="leaf" size="lg" />
            </div>
            <div class="absolute bottom-20 right-20">
                <x-tea-decoration type="bamboo" size="md" />
            </div>
            <div class="absolute top-1/3 right-1/4">
                <x-tea-decoration type="ceramic" size="sm" />
            </div>
            <div class="absolute bottom-1/3 left-1/3">
                <x-tea-decoration type="wave" size="md" />
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto px-6 md:px-8 flex flex-col md:flex-row items-center justify-between gap-12 md:gap-16 relative z-10">
            <div class="flex-1 max-w-2xl">
                <div class="tea-decoration mb-8">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6 tea-title">
                        {{ __('home.hero_title') }}
                    </h1>
                </div>
                <p class="text-lg md:text-xl text-gray-600 mb-10 font-normal leading-relaxed max-w-xl">
                    {{ __('home.hero_subtitle') }}
                </p>
                <a href="{{ locaRoute('product') }}"
                    class="inline-block tea-btn-primary font-semibold px-8 py-4 rounded-lg text-base shadow-lg hover:shadow-xl">
                    {{ __('home.shop_now') }}
                </a>
            </div>
            <div class="flex-1 hidden md:block">
                <div class="relative">
                    <img src="{{ asset('images/banner-tea.png') }}" alt="{{ __('home.hero_image_alt') }}"
                        class="w-full h-auto rounded-2xl shadow-2xl tea-float">
                    <!-- 茶文化装饰边框 -->
                    <div class="absolute -inset-4 bg-gradient-to-r from-tea-200 via-bamboo-200 to-ceramic-200 rounded-3xl opacity-30 -z-10"></div>
                </div>
            </div>
        </div>
    </section>

    <x-promotion-list class="px-6" parentClass="bg-tea-100 py-8 tea-bg-texture" />

    {{-- Featured Products --}}
    <section class="max-w-7xl mx-auto px-6 md:px-8 py-20 md:py-24">
        <div class="text-center mb-16">
            <div class="tea-decoration">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 tea-title">{{ __('home.featured_products') }}</h2>
            </div>
            <p class="text-gray-600 text-base md:text-lg max-w-2xl mx-auto">{{ __('home.featured_products_subtitle') }}</p>
        </div>
        @livewire('components.featured-products')
    </section>

    {{-- Categories Quick Access --}}
    <section class="bg-white py-20 md:py-24 relative">
        <div class="max-w-7xl mx-auto px-6 md:px-8">
            <div class="text-center mb-16">
                <div class="tea-decoration">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 tea-title">{{ __('home.browse_categories') }}</h2>
                </div>
                <p class="text-gray-600 text-base md:text-lg max-w-2xl mx-auto">{{ __('home.browse_categories_subtitle') }}</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 md:gap-8">
                @foreach ($categories as $category)
                    <a href="{{ locaRoute('product', ['slug' => $category['slug']]) }}" class="group">
                        <div class="tea-card rounded-xl p-6 text-center">
                            <div class="relative mb-4">
                                <img src="{{ $category['image_url'] }}" alt="{{ $category['name'] }}"
                                    class="h-20 w-20 mx-auto object-cover rounded-full border-2 border-tea-200 group-hover:border-tea-400 transition-colors">
                                <div class="absolute inset-0 rounded-full bg-tea-100 opacity-0 group-hover:opacity-20 transition-opacity"></div>
                            </div>
                            <span class="text-tea-800 font-medium block group-hover:text-tea-600 transition-colors">{{ $category['name'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 md:py-24 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-6 md:px-8">
            <div class="text-center mb-16">
                <div class="tea-decoration">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 tea-title">{{ __('home.tea_culture_articles') }}</h2>
                </div>
                <p class="text-gray-600 text-base md:text-lg max-w-2xl mx-auto">{{ __('home.tea_culture_articles_subtitle') }}</p>
            </div>
            <livewire:components.random-articles :limit="6" />
        </div>
    </section>

    {{-- About Store --}}
    <section class="py-20 md:py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6 md:px-8 flex flex-col md:flex-row items-center gap-12 md:gap-16">
            <div class="md:w-1/2">
                <div class="tea-decoration mb-8">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 tea-title">{{ __('home.about_title') }}</h2>
                </div>
                <p class="text-base md:text-lg text-gray-600 leading-relaxed mb-10 font-normal">
                    {{ __('home.about_content') }}
                </p>
                <a href="{{ locaRoute('article.index') }}"
                    class="inline-block tea-btn-primary font-semibold px-8 py-4 rounded-lg text-base shadow-lg hover:shadow-xl">
                    {{ __('home.learn_more') }}
                </a>
            </div>
            <div class="hidden md:block md:w-1/2">
                <div class="relative">
                    <img src="{{ asset('images/about-banner.jpg') }}" class="rounded-2xl shadow-2xl">
                    <div class="absolute -inset-4 bg-gradient-to-r from-tea-200 via-bamboo-200 to-ceramic-200 rounded-3xl opacity-30 -z-10"></div>
                </div>
            </div>
        </div>
    </section>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('app.site_title') }}" description="{{ __('app.site_description') }}"
        keywords="{{ __('app.site_keywords') }}" />
@endPushOnce
