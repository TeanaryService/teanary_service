<div x-data x-init="$wire.loadRecommendedProducts()">
    @if (!$loaded)
        <div>加载推荐中...</div>
    @else
        @if ($recommendedProducts->isEmpty())
            <p>暂无推荐产品</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-10">
                @foreach ($recommendedProducts as $product)
                    <x-product-item :product="$product" />
                @endforeach
            </div>
        @endif
    @endif
</div>
