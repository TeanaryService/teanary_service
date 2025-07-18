<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach ($products as $product)
        <x-product-item :product="$product" />
    @endforeach
</div>
