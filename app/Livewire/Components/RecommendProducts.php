<?php

namespace App\Livewire\Components;

use App\Models\Product;
use Livewire\Component;

class RecommendProducts extends Component
{
    public $currentProductId;

    public $categoryIds = [];

    public $recommendedProducts = [];

    public $loaded = false;  // 是否已加载推荐

    protected $listeners = ['loadRecommendedProducts']; // 监听事件

    public function mount($currentProductId, $categoryIds = [])
    {
        $this->currentProductId = $currentProductId;
        $this->categoryIds = $categoryIds;
    }

    public function loadRecommendedProducts()
    {
        if ($this->loaded) {
            return;
        }

        $this->recommendedProducts = Product::with(['productTranslations', 'media', 'productVariants'])
            ->active()
            ->forWarehouse(session('warehouse_id'))
            ->whereHas('productCategories', function ($q) {
                $q->whereIn('id', $this->categoryIds);
            })
            ->where('id', '!=', $this->currentProductId)
            ->latest('created_at')
            ->limit(4)
            ->get();

        $this->loaded = true;
    }

    public function render()
    {
        return view('livewire.components.recommend-products');
    }
}
