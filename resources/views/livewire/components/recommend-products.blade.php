<div x-data x-init="$wire.loadRecommendedProducts().then(() => { 
    // 在数据加载完成后，等待DOM更新完成再初始化懒加载
    setTimeout(() => window.updateLazyLoad(), 100);
})">
    @if (!$loaded)
        <div>加载推荐中...</div>
    @else
        @if ($recommendedProducts->isEmpty())
            <p>暂无推荐产品</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-10">
                @foreach ($recommendedProducts as $product)
                    <x-widgets.product-item :product="$product" />
                @endforeach
            </div>
        @endif
    @endif
</div>
