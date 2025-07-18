@php
    $locale = app()->getLocale();
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
    $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
    $name = $translation && $translation->name ? $translation->name : ($product->productTranslations->first()->name ?? $product->slug);
    $variant = $product->productVariants->first();
    $image = $variant ? $variant->getFirstMediaUrl('image', 'thumb') : asset('logo.png');
@endphp
<div class="bg-white rounded shadow p-4 text-center hover:bg-green-50 transition">
    <a href="{{ route('product.show', ['id' => $product->id]) }}">
        <img src="{{ $image }}"
             alt="{{ $name }}"
             class="h-auto w-auto mx-auto mb-2 object-cover rounded">
        <span class="text-green-900 font-semibold block mb-1">{{ $name }}</span>
        <span class="text-gray-500 text-sm">{{ __('home.product_view_detail') }}</span>
    </a>
</div>
