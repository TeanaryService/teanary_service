<div class="bg-green-50">

    {{-- Hero Banner --}}
    <section class="w-full bg-green-700 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex-1">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
                    {{ __('home.hero_title') }}
                </h1>
                <p class="text-lg mb-6">
                    {{ __('home.hero_subtitle') }}
                </p>
                <a href="{{ locaRoute('product') }}"
                    class="inline-block bg-white text-green-700 font-bold px-6 py-3 rounded hover:bg-green-100 transition">
                    {{ __('home.shop_now') }}
                </a>
            </div>
            <div class="flex-1">
                <img src="{{ asset('images/banner-flowers.png') }}" alt="{{ __('home.hero_image_alt') }}"
                    class="w-full h-auto rounded-xl shadow-lg">
            </div>
        </div>
    </section>

    {{-- Categories Quick Access --}}
    <section class="max-w-7xl mx-auto py-12 px-4">
        <h2 class="text-2xl font-bold text-green-700 mb-6">{{ __('home.browse_categories') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach ($categories as $category)
                <a href="{{ locaRoute('product', ['category_id' => $category['id']]) }}"
                    class="bg-white rounded shadow p-4 text-center hover:bg-green-50 transition">
                    <img src="{{ $category['image_url'] }}" alt="{{ $category['name'] }}"
                        class="h-auto w-auto mx-auto mb-2 object-cover rounded">
                    <span class="text-green-900 font-semibold">{{ $category['name'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Featured Products --}}
    <section class="max-w-7xl mx-auto py-12 px-4">
        <h2 class="text-2xl font-bold text-green-700 mb-6">{{ __('home.featured_products') }}</h2>
        @livewire('components.featured-products')
    </section>

    {{-- About Store --}}
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <h2 class="text-3xl font-bold text-green-700 mb-4">{{ __('home.about_title') }}</h2>
                <p class="text-green-900 mb-4">
                    {{ __('home.about_content') }}
                </p>
                <a href="{{ locaRoute('about-us') }}"
                    class="inline-block bg-green-600 text-white px-5 py-3 rounded hover:bg-green-700 transition">
                    {{ __('home.learn_more') }}
                </a>
            </div>
        </div>
    </section>

</div>

@pushOnce('tdk')
    <x-layouts.tdk title="{{ __('app.site_title') }}" description="{{ __('app.site_description') }}"
        keywords="{{ __('app.site_keywords') }}" />
@endPushOnce
