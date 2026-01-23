<?php

namespace App\Livewire\Components;

use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Product;
use Livewire\Component;

class RandomProducts extends Component
{
    use UsesLocaleCurrency;

    public $limit = 4;

    public $class = 'grid-cols-2 md:grid-cols-4';  // 默认网格布局

    private $products = [];

    public function mount($limit = 4, $class = null)
    {
        $this->limit = $limit;
        $langId = $this->getCurrentLanguage()?->id;

        if ($class) {
            $this->class = $class;
        }

        $this->products = Product::with([
            'productTranslations',
            'productVariants.media',
            'media',
        ])
            ->active()
            ->inRandomOrder()
            ->take($this->limit)
            ->get();
    }

    public function render()
    {
        return view('livewire.components.random-products', [
            'products' => $this->products,
        ]);
    }
}
