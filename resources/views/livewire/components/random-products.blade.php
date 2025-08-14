<div class="mb-10">
    <div class="grid gap-6 {{ $class }}">
        @forelse ($products as $product)
            <x-product-item :product="$product" />
        @empty
            <div class="col-span-4 text-center text-gray-500 py-12">
                {{ __('home.no_products') }}
            </div>
        @endforelse
    </div>
</div>
