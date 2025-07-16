<div class="bg-green-50">

    {{-- Hero Banner --}}
    <section class="w-full bg-green-700 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex-1">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
                    Fresh Cut Flowers Delivered to Your Door
                </h1>
                <p class="text-lg mb-6">
                    Discover premium blooms and elevate every occasion with KM Flora.
                </p>
                Shop Now
                {{-- <a href="{{ route('shop.index') }}"
                   class="inline-block bg-white text-green-700 font-bold px-6 py-3 rounded hover:bg-green-100 transition">
                    Shop Now
                </a> --}}
            </div>
            <div class="flex-1">
                <img src="{{ asset('images/banner-flowers.png') }}"
                     alt="Bouquet of flowers"
                     class="w-full h-auto rounded shadow-lg">
            </div>
        </div>
    </section>

    {{-- Categories Quick Access --}}
    <section class="max-w-7xl mx-auto py-12 px-4">
        <h2 class="text-2xl font-bold text-green-700 mb-6">Browse Categories</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
            Category
            {{-- @foreach($categories as $category)
                <a href="{{ route('category.show', $category->slug) }}"
                   class="bg-white rounded shadow p-4 text-center hover:bg-green-50 transition">
                    <img src="{{ $category->image_url ?? asset('images/default-category.png') }}"
                         alt="{{ $category->name }}"
                         class="h-24 mx-auto mb-2 object-cover rounded">
                    <span class="text-green-900 font-semibold">{{ $category->name }}</span>
                </a>
            @endforeach --}}
        </div>
    </section>

    {{-- Featured Products --}}
    <section class="max-w-7xl mx-auto py-12 px-4">
        <h2 class="text-2xl font-bold text-green-700 mb-6">Featured Products</h2>
        @livewire('featured-products')
    </section>

    {{-- About Store --}}
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <h2 class="text-3xl font-bold text-green-700 mb-4">About KM Flora</h2>
                <p class="text-green-900 mb-4">
                    KM Flora offers a wide range of fresh cut flowers sourced from trusted growers. Whether for daily joy or special events, we deliver beauty directly to your door.
                </p>
                <a>关于我们</a>
                {{-- <a href="{{ route('about') }}"
                   class="inline-block bg-green-600 text-white px-5 py-3 rounded hover:bg-green-700 transition">
                    Learn More
                </a> --}}
            </div>
        </div>
    </section>

</div>


@pushOnce('tdk')
    <x-layouts.tdk title="{{ __('app.site_title') }}"
        description="{{ __('app.site_description') }}"
        keywords="{{ __('app.site_keywords') }}" />
@endPushOnce
