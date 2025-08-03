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
    
    // 获取产品的所有图片
    $images = $product->getMedia('images');
    $firstImage = $images->first()?->getUrl('thumb') ?? $product->getFirstMediaUrl('images', 'thumb');
    $secondImage = $images->count() > 1 ? $images->get(1)?->getUrl('thumb') : null;
    
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

<div class="group bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
    <a href="{{ locaRoute('product.show', ['slug' => $product->slug]) }}" class="block">
        <div class="relative w-full aspect-square bg-gray-100 overflow-hidden">
            <!-- 第一张图片 -->
            <img src="{{ $firstImage }}" alt="{{ $name }}"
                 class="absolute inset-0 w-full h-full object-cover object-center transition-all duration-500 group-hover:scale-105 {{ $secondImage ? 'group-hover:opacity-0' : '' }}">
            
            <!-- 第二张图片（如果存在） -->
            @if ($secondImage)
                <img src="{{ $secondImage }}" alt="{{ $name }}"
                     class="absolute inset-0 w-full h-full object-cover object-center transition-all duration-500 opacity-0 scale-105 group-hover:opacity-100 group-hover:scale-110">
            @endif
            
            <!-- 图片指示器（如果有多张图片） -->
            @if ($secondImage)
                <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="w-1.5 h-1.5 bg-white/80 rounded-full group-hover:bg-white"></div>
                    <div class="w-1.5 h-1.5 bg-white/40 rounded-full group-hover:bg-white/60"></div>
                </div>
            @endif
            
            <!-- 悬停遮罩效果 -->
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-all duration-300"></div>
        </div>
        
        <div class="p-2 md:p-4">
            <h3 class="line-clamp-2 text-lg font-medium text-gray-900 mb-2 group-hover:text-teal-700 transition-colors duration-200">
                {{ $name }}
            </h3>
            @if ($priceText)
                <p class="text-lg font-bold text-teal-600 group-hover:text-teal-700 transition-colors duration-200">
                    {{ $priceText }}
                </p>
            @endif
            <p class="mt-2 text-sm text-gray-500 group-hover:text-gray-700 transition-colors duration-200">
                {{ __('home.product_view_detail') }}
            </p>
            
            <!-- 多图提示 -->
            @if ($secondImage)
                <div class="mt-2 flex items-center gap-1 text-xs text-gray-400 group-hover:text-teal-600 transition-colors duration-200">
                    <x-heroicon-o-photo class="w-4 h-4" />
                    <span>{{ $images->count() }} {{ __('app.images') }}</span>
                </div>
            @endif
        </div>
    </a>
</div>