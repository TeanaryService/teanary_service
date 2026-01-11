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

    public function mount(string $slug): void
    {
        $localeCurrencyService = app(LocaleCurrencyService::class);
        $lang = $localeCurrencyService->getLanguageByCode(session('lang'));

        $this->product = Product::with([
            'media',
            'productTranslations',
            'productVariants.specificationValues.specificationValueTranslations',
            'productVariants' => fn ($q) => $q->orderBy('price'),
            'productVariants.media',
            'productCategories.categoryTranslations',
            'attributeValues.attributeValueTranslations',
            'attributeValues.attribute.attributeTranslations',
        ])->active()->where('slug', $slug)->firstOrFail();

        $this->variants = $this->product->productVariants;

        // 默认选第一个规格
        $this->selectedVariantId = $this->variants->first()?->id;

        // 获取可用促销信息
        $this->updateAvailablePromotions();

        // 分类名（多语言）
        $this->categoryNames = $this->getCategoryNames($lang);
    }

    public function selectVariant(int $variantId): void
    {
        $this->selectedVariantId = $variantId;
        $this->updateAvailablePromotions();
        $this->qty = 1;
    }

    public function updateQty(int|string $qty): void
    {
        $variant = $this->getSelectedVariant();
        $max = $variant?->stock ?? 1;
        $this->qty = max(1, min((int) $qty, $max));
    }

    public function decrementQty(): void
    {
        $this->qty = max(1, $this->qty - 1);
    }

    public function incrementQty(): void
    {
        $variant = $this->getSelectedVariant();
        $max = $variant?->stock ?? 1;
        $this->qty = min($max, $this->qty + 1);
    }

    public function buyNow()
    {
        $variant = $this->getSelectedVariant();
        $max = $variant?->stock ?? 1;
        $qty = max(1, min($this->qty, $max));

        session()->put('checkout_items', [[
            'cart_item_id' => 0,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->selectedVariantId,
            'qty' => $qty,
        ]]);

        return redirect()->route('checkout', ['locale' => app()->getLocale()]);
    }

    public function render()
    {
        $localeCurrencyService = app(LocaleCurrencyService::class);
        $lang = $localeCurrencyService->getLanguageByCode(session('lang'));
        $currencyCode = session('currency');
        $variant = $this->getSelectedVariant();
        $finalPrice = $variant?->price ?? 0;

        // 准备视图数据
        $translation = $this->product->productTranslations
            ->where('language_id', $lang->id)
            ->first();
        $name = $translation?->name ?? $this->product->slug;
        $desc = $translation?->description ?? '';
        $shortDesc = $translation?->short_description ?? '';
        $images = $this->product->getMedia('images');
        $price = $finalPrice
            ? $localeCurrencyService->convertWithSymbol($finalPrice, $currencyCode)
            : ($variant?->price
                ? $localeCurrencyService->convertWithSymbol($variant->price, $currencyCode)
                : '');
        $attributes = $this->product->attributeValues ?? collect();
        $maxQty = $variant?->stock ?? 1;

        // 准备结构化数据
        $structuredData = $this->buildStructuredData($name, $shortDesc, $images, $variant, $currencyCode, $price, $attributes, $lang);

        return view('livewire.product-detail', [
            'product' => $this->product,
            'variants' => $this->variants,
            'selectedVariantId' => $this->selectedVariantId,
            'categoryNames' => $this->categoryNames,
            'qty' => $this->qty,
            'availablePromotions' => $this->availablePromotions,
            'finalPrice' => $finalPrice,
            'name' => $name,
            'desc' => $desc,
            'shortDesc' => $shortDesc,
            'images' => $images,
            'price' => $price,
            'attributes' => $attributes,
            'maxQty' => $maxQty,
            'structuredData' => $structuredData,
            'lang' => $lang,
            'currencyCode' => $currencyCode,
            'currencyService' => $localeCurrencyService,
        ]);
    }

    /**
     * 获取当前选中的规格.
     */
    protected function getSelectedVariant()
    {
        if (! $this->selectedVariantId) {
            return null;
        }

        return $this->variants->firstWhere('id', $this->selectedVariantId);
    }

    /**
     * 更新可用促销信息.
     */
    protected function updateAvailablePromotions(): void
    {
        $variant = $this->getSelectedVariant();
        if (! $variant) {
            $this->availablePromotions = [];

            return;
        }

        $promotionService = app(PromotionService::class);
        $this->availablePromotions = $promotionService->getAvailablePromotionsForVariant(
            $variant,
            auth()->user()
        );
    }

    /**
     * 获取分类名称（多语言）.
     */
    protected function getCategoryNames($lang): array
    {
        return $this->product->productCategories->map(function ($cat) use ($lang) {
            $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();

            return $translation?->name ?? $cat->slug;
        })->toArray();
    }

    /**
     * 构建结构化数据（用于 SEO）.
     */
    protected function buildStructuredData(
        string $name,
        string $shortDesc,
        $images,
        $variant,
        string $currencyCode,
        string $price,
        $attributes,
        $lang
    ): array {
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'description' => $shortDesc,
            'image' => $images->first()?->getUrl(),
            'sku' => $variant?->sku,
        ];

        if ($price) {
            $structuredData['offers'] = [
                '@type' => 'Offer',
                'url' => url()->current(),
                'priceCurrency' => $currencyCode,
                'price' => str_replace(['$', '€', '£', '¥'], '', $price),
                'availability' => $variant && $variant->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ];
        }

        if ($attributes->count()) {
            $structuredData['additionalProperty'] = $attributes
                ->map(function ($attrValue) use ($lang) {
                    $attrTrans = $attrValue->attribute->attributeTranslations
                        ->where('language_id', $lang?->id)
                        ->first();
                    $attrValueTrans = $attrValue->attributeValueTranslations
                        ->where('language_id', $lang?->id)
                        ->first();

                    $attrName = $attrTrans?->name ?? $attrValue->attribute->id;
                    $attrValueName = $attrValueTrans?->name ?? $attrValue->id;

                    return [
                        '@type' => 'PropertyValue',
                        'name' => "{$attrName}: {$attrValueName}",
                    ];
                })
                ->values()
                ->all();
        }

        return $structuredData;
    }
}
