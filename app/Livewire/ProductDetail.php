<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Services\LocaleCurrencyService;

class ProductDetail extends Component
{
    public $product;
    public $variants;
    public $selectedVariantId;
    public $categoryNames = [];

    public function mount($id)
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(session('lang'));

        $this->product = Product::with([
            'productTranslations',
            'productVariants.specificationValues.specificationValueTranslations',
            'productVariants.media',
            'productCategories.categoryTranslations'
        ])->findOrFail($id);

        $this->variants = $this->product->productVariants;

        // 默认选第一个规格
        $this->selectedVariantId = $this->variants->first()?->id;

        // 分类名（多语言）
        $this->categoryNames = $this->product->productCategories->map(function ($cat) use ($lang) {
            $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
            return $translation && $translation->name ? $translation->name : $cat->slug;
        })->toArray();
    }

    public function selectVariant($variantId)
    {
        $this->selectedVariantId = $variantId;
    }

    public function render()
    {
        return view('livewire.product-detail', [
            'product' => $this->product,
            'variants' => $this->variants,
            'selectedVariantId' => $this->selectedVariantId,
            'categoryNames' => $this->categoryNames,
        ]);
    }
}
