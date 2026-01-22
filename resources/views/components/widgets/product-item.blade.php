@php
    $productData = getProductDisplayData($product);
    $name = $productData['name'];
    $firstImage = $productData['firstImage'];
    $secondImage = $productData['secondImage'];
    $priceText = $productData['priceText'];
    $images = $productData['images'];
@endphp

<div class="group tea-product-card">
    <a href="{{ locaRoute('product.show', ['slug' => $product->slug]) }}" wire:navigate class="block">
        <div class="relative w-full aspect-square bg-gray-100 overflow-hidden rounded-t-lg">
            <!-- 第一张图片 -->
            <img src="{{ $firstImage }}" 
                 alt="{{ $name }}"
                 class="absolute inset-0 w-full h-full object-cover object-center transition-all duration-500 group-hover:scale-110 {{ $secondImage ? 'group-hover:opacity-0' : '' }}">
            
            <!-- 第二张图片（如果存在） -->
            @if ($secondImage)
                <img src="{{ $secondImage }}" 
                     alt="{{ $name }}"
                     class="absolute inset-0 w-full h-full object-cover object-center transition-all duration-500 opacity-0 scale-110 group-hover:opacity-100 group-hover:scale-115">
            @endif
            
            <!-- 图片指示器（如果有多张图片） -->
            @if ($secondImage)
                <div class="absolute bottom-3 right-3 flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="w-2 h-2 bg-white/90 rounded-full shadow-sm"></div>
                    <div class="w-2 h-2 bg-white/50 rounded-full shadow-sm"></div>
                </div>
            @endif
            
            <!-- 悬停遮罩效果 -->
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-all duration-300"></div>
        </div>
        
        <div class="p-4 md:p-5">
            <h3 class="line-clamp-2 text-base md:text-lg font-semibold text-gray-900 mb-3 group-hover:text-tea-600 transition-colors duration-200 leading-snug">
                {{ $name }}
            </h3>
            @if ($priceText)
                <p class="text-lg md:text-xl font-bold tea-price mb-3 group-hover:text-tea-700 transition-colors duration-200">
                    {{ $priceText }}
                </p>
            @endif
            <p class="text-sm text-gray-500 group-hover:text-gray-600 transition-colors duration-200">
                {{ __('home.product_view_detail') }}
            </p>
            
            <!-- 多图提示 -->
            @if ($secondImage)
                <div class="mt-2 flex items-center gap-1 text-xs text-tea-500 group-hover:text-tea-600 transition-colors duration-200">
                    <x-heroicon-o-photo class="w-4 h-4" />
                    <span>{{ $images->count() }} {{ __('app.images') }}</span>
                </div>
            @endif
        </div>
    </a>
</div>