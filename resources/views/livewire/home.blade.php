<div class="bg-gray-50">

    {{-- Hero Banner --}}
    <section class="w-full bg-white py-20">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-12">
            <div class="flex-1 max-w-xl">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-900 leading-tight mb-6">
                    {{ __('home.hero_title') }}
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    {{ __('home.hero_subtitle') }}
                </p>
                <a href="{{ locaRoute('product') }}"
                    class="inline-block bg-green-600 text-white font-medium px-8 py-4 rounded-lg hover:bg-green-700 transition-colors">
                    {{ __('home.shop_now') }}
                </a>
            </div>
            <div class="flex-1">
                <img src="{{ asset('images/banner-flowers.png') }}" alt="{{ __('home.hero_image_alt') }}"
                    class="w-full h-auto rounded-2xl shadow-xl">
            </div>
        </div>
    </section>

    {{-- Categories Quick Access --}}
    <section class="max-w-7xl mx-auto py-20 px-6">
        <h2 class="text-3xl font-bold text-gray-900 mb-12">{{ __('home.browse_categories') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
            @foreach ($categories as $category)
                <a href="{{ locaRoute('product', ['category_id' => $category['id']]) }}"
                    class="group">
                    <div class="bg-white rounded-xl shadow-sm p-6 transition duration-200 group-hover:shadow-md">
                        <img src="{{ $category['image_url'] }}" alt="{{ $category['name'] }}"
                            class="h-auto w-auto mx-auto mb-4 object-cover rounded-lg">
                        <span class="text-gray-900 font-medium block text-center group-hover:text-green-600">{{ $category['name'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Featured Products --}}
    <section class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-gray-900 mb-12">{{ __('home.featured_products') }}</h2>
            @livewire('components.featured-products')
        </div>
    </section>

    {{-- About Store --}}
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center gap-12">
            <div class="md:w-1/2">
                <h2 class="text-4xl font-bold text-gray-900 mb-6">{{ __('home.about_title') }}</h2>
                <p class="text-xl text-gray-600 leading-relaxed mb-8">
                    {{ __('home.about_content') }}
                </p>
                <a href="{{ locaRoute('about-us') }}"
                    class="inline-block bg-green-600 text-white font-medium px-8 py-4 rounded-lg hover:bg-green-700 transition-colors">
                    {{ __('home.learn_more') }}
                </a>
            </div>
            <div class="md:w-1/2 bg-gray-100 rounded-2xl p-12">
                <x-layouts.logo imgClass="w-full max-w-sm mx-auto" :showText="false"/>
            </div>
        </div>
    </section>
</div>

@pushOnce('tdk')
    <x-layouts.tdk title="{{ __('app.site_title') }}" description="{{ __('app.site_description') }}"
        keywords="{{ __('app.site_keywords') }}" />
@endPushOnce
