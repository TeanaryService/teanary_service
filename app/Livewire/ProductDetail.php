<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\LocaleCurrencyService;
use App\Services\PromotionService;
use Livewire\Component;

class ProductDetail extends Component
{
    public $product;

    public $variants;

    public $selectedVariantId;

    public $categoryNames = [];

    public $qty = 1;

    public $availablePromotions = [];

    public function mount($slug)
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(session('lang'));

        $this->product = Product::with([
            'media',
            'productTranslations',
            'productVariants.specificationValues.specificationValueTranslations',
            'productVariants' => fn ($q) => $q->orderBy('price'),
            'productVariants.media',
            'productCategories.categoryTranslations',
            'attributeValues.attributeValueTranslations',
            'attributeValues.attribute.attributeTranslations',
        ])->where('slug', $slug)->firstOrFail();

        $this->variants = $this->product->productVariants;

        // 默认选第一个规格
        $this->selectedVariantId = $this->variants->first()?->id;

        // 获取可用促销信息
        if ($this->selectedVariantId) {
            $variant = $this->variants->where('id', $this->selectedVariantId)->first();
            $promoService = app(PromotionService::class);
            $this->availablePromotions = $promoService->getAvailablePromotionsForVariant($variant, auth()->user());
        }

        // 分类名（多语言）
        $this->categoryNames = $this->product->productCategories->map(function ($cat) use ($lang) {
            $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();

            return $translation && $translation->name ? $translation->name : $cat->slug;
        })->toArray();
    }

    public function selectVariant($variantId)
    {
        $this->selectedVariantId = $variantId;

        // 更新当前规格的可用促销
        $variant = $this->variants->where('id', $variantId)->first();
        $promoService = app(PromotionService::class);
        $this->availablePromotions = $promoService->getAvailablePromotionsForVariant($variant, auth()->user());

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
            'cart_item_id' => 0,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->selectedVariantId,
            'qty' => $qty,
        ]];

        session()->put('checkout_items', $selectedItems);
        $locale = app()->getLocale();

        return redirect()->route('checkout', ['locale' => $locale]);
    }

    public function render()
    {
        $variant = $this->variants->where('id', $this->selectedVariantId)->first();
        $finalPrice = $variant?->price ?? 0;

        return view('livewire.product-detail', [
            'product' => $this->product,
            'variants' => $this->variants,
            'selectedVariantId' => $this->selectedVariantId,
            'categoryNames' => $this->categoryNames,
            'qty' => $this->qty,
            'availablePromotions' => $this->availablePromotions,
            'finalPrice' => $finalPrice,
        ]);
    }
}
