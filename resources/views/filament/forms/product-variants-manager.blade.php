@if($productId ?? null)
    @livewire('manage-product-variants', ['productId' => $productId], key('manage-product-variants-' . $productId))
@else
    <div class="p-4 text-center text-gray-500">
        {{ __('filament.product_variant_manage.save_product_first') }}
    </div>
@endif
