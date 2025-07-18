<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Product;

class FeaturedProducts extends Component
{
    public $products = [];

    public function mount()
    {
        $this->products = Product::with(['productTranslations', 'productVariants.media'])
            ->latest('id')
            ->take(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.components.featured-products', [
            'products' => $this->products,
        ]);
    }
}
