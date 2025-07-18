@php
    $locale = app()->getLocale();
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency'); // 可根据实际获取当前币种
    $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
    $name =
        $translation && $translation->name
            ? $translation->name
            : $product->productTranslations->first()->name ?? $product->slug;
    $variants = $product->productVariants;
    $image = $variants->first() ? $variants->first()->getFirstMediaUrl('image', 'thumb') : asset('logo.png');
    $prices = $variants->pluck('price')->filter()->sort()->values();
    if ($prices->count() === 1) {
        $converted = $currencyService->convertWithSymbol(amount: $prices->first(), toCode: $currencyCode);
        $priceText = $converted;
    } elseif ($prices->count() > 1) {
        $min = $currencyService->convertWithSymbol(amount: $prices->first(), toCode: $currencyCode);
        $max = $currencyService->convertWithSymbol(amount: $prices->last(), toCode: $currencyCode);
        $priceText = $min . ' - ' . $max;
    } else {
        $priceText = '';
    }
@endphp
<div class="bg-white rounded shadow p-4 text-center hover:bg-green-50 transition">
    <a href="{{ route('product.show', ['id' => $product->id]) }}">
        <img src="{{ $image }}" alt="{{ $name }}" class="h-auto w-auto mx-auto mb-2 object-cover rounded">
        <span class="text-green-900 font-semibold block mb-1">{{ $name }}</span>
        @if ($priceText)
            <span class="text-green-700 font-bold block mb-1">{{ $priceText }}</span>
        @endif
        <span class="text-gray-500 text-sm">{{ __('home.product_view_detail') }}</span>
    </a>
</div>
