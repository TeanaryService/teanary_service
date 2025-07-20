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
    public $qty = 1;

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
        // 切换规格时重置数量为1
        $this->qty = 1;
    }

    public function updateQty($qty)
    {
        $variant = $this->variants->where('id', $this->selectedVariantId)->first();
        $max = $variant ? $variant->stock : 1;
        $this->qty = max(1, min($qty, $max));
    }

    public function decrementQty()
    {
        $variant = $this->variants->where('id', $this->selectedVariantId)->first();
        $min = 1;
        $this->qty = max($min, $this->qty - 1);
    }

    public function incrementQty()
    {
        $variant = $this->variants->where('id', $this->selectedVariantId)->first();
        $max = $variant ? $variant->stock : 1;
        $this->qty = min($max, $this->qty + 1);
    }

    public function buyNow()
    {
        $variant = $this->variants->where('id', $this->selectedVariantId)->first();
        $max = $variant ? $variant->stock : 1;
        $qty = max(1, min($this->qty, $max));

        $selectedItems = [[
            'product_id' => $this->product->id,
            'product_variant_id' => $this->selectedVariantId,
            'qty' => $qty,
        ]];

        session()->flash('checkout_items', $selectedItems);
        $locale = app()->getLocale();
        return redirect()->route('checkout', ['locale' => $locale]);
    }

    public function render()
    {
        return view('livewire.product-detail', [
            'product' => $this->product,
            'variants' => $this->variants,
            'selectedVariantId' => $this->selectedVariantId,
            'categoryNames' => $this->categoryNames,
            'qty' => $this->qty,
        ]);
    }
}
