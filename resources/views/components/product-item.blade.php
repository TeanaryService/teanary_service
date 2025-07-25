@php
    $locale = session('lang');
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency'); // 可根据实际获取当前币种
    $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
    $name =
        $translation && $translation->name
            ? $translation->name
            : $product->productTranslations->first()->name ?? $product->slug;
    $variants = $product->productVariants;
    $image = $product->getFirstMediaUrl('images', 'thumb');
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

<div class="group bg-white rounded-xl shadow-sm hover:shadow-md transition duration-200 overflow-hidden">
    <a href="{{ locaRoute('product.show', ['id' => $product->id]) }}" class="block">
        <div class="aspect-w-1 aspect-h-1 bg-gray-100">
            <img src="{{ $image }}" alt="{{ $name }}" 
                class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-300">
        </div>
        <div class="p-2 md:p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $name }}</h3>
            @if ($priceText)
                <p class="text-lg font-bold text-teal-600">{{ $priceText }}</p>
            @endif
            <p class="mt-2 text-sm text-gray-500">{{ __('home.product_view_detail') }}</p>
        </div>
    </a>
</div>
