<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\LocaleCurrencyService;
use App\Services\ProductVariantService;
use App\Services\PromotionService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductDetail extends Component
{
    public $product;

    public $variants;

    public $selectedVariantId;

    public $categoryNames = [];

    public $qty = 1;

    public $availablePromotions = [];

    // 已选择的规格值 [specification_id => specification_value_id]
    public $selectedOptions = [];

    protected ProductVariantService $variantService;

    public function boot(): void
    {
        $this->variantService = app(ProductVariantService::class);
    }

    /**
     * 获取所有已选择的规格和规格值（用于生成笛卡尔积）
     */
    #[Computed]
    public function specificationsForSelection()
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(session('lang'));
        
        // 获取该商品所有 SKU 使用的规格 ID（从 pivot 表中获取）
        $usedSpecificationIds = $this->product->productVariants
            ->flatMap(function ($variant) {
                return $variant->specificationValues->map(function ($sv) {
                    return (int)($sv->pivot->specification_id ?? $sv->specification_id);
                });
            })
            ->unique()
            ->values()
            ->toArray();
        
        // 获取所有已使用的规格值，按规格分组
        $specGroups = [];
        foreach ($this->product->productVariants as $variant) {
            foreach ($variant->specificationValues as $sv) {
                $specId = (int)($sv->pivot->specification_id ?? $sv->specification_id);
                
                // 只处理在后台 SKU 管理中已选择的规格
                if (!in_array($specId, $usedSpecificationIds)) {
                    continue;
                }
                
                if (!isset($specGroups[$specId])) {
                    $spec = $sv->specification ?? $sv->specification()->with('specificationTranslations')->first();
                    $specTrans = $spec?->specificationTranslations
                        ->where('language_id', $lang?->id)
                        ->first();
                    $specName = $specTrans && $specTrans->name 
                        ? $specTrans->name 
                        : ($spec?->specificationTranslations->first()->name ?? $specId);
                    
                    $specGroups[$specId] = [
                        'id' => $specId,
                        'name' => $specName,
                        'values' => [],
                    ];
                }
                
                // 获取规格值名称
                $valueTrans = $sv->specificationValueTranslations
                    ->where('language_id', $lang?->id)
                    ->first();
                $valueName = $valueTrans && $valueTrans->name 
                    ? $valueTrans->name 
                    : ($sv->specificationValueTranslations->first()->name ?? $sv->id);
                
                $valueId = $sv->id;
                
                // 避免重复添加相同的规格值
                if (!isset($specGroups[$specId]['values'][$valueId])) {
                    $specGroups[$specId]['values'][$valueId] = [
                        'id' => $valueId,
                        'name' => $valueName,
                    ];
                }
            }
        }
        
        // 按规格 ID 排序
        ksort($specGroups);
        // 对每个规格的规格值也排序
        foreach ($specGroups as &$group) {
            ksort($group['values']);
            $group['values'] = array_values($group['values']);
        }
        
        return array_values($specGroups);
    }

    /**
     * 根据规格值组合找到对应的 SKU
     */
    protected function findVariantBySpecificationValues(array $specValueIds): ?int
    {
        return $this->variantService->findVariantBySpecificationValues($this->variants, $specValueIds);
    }

    /**
     * 获取所有 SKU 组合（笛卡尔积）
     */
    #[Computed]
    public function skuCombinations()
    {
        $specs = $this->specificationsForSelection;
        
        if (empty($specs)) {
            return [];
        }
        
        // 构建规格值数组
        $specValueArrays = [];
        foreach ($specs as $spec) {
            $values = [];
            foreach ($spec['values'] as $value) {
                $values[] = [
                    'specification_id' => $spec['id'],
                    'specification_value_id' => $value['id'],
                    'specification_name' => $spec['name'],
                    'specification_value_name' => $value['name'],
                ];
            }
            $specValueArrays[] = $values;
        }
        
        // 生成笛卡尔积
        $combinations = $this->variantService->cartesianProduct($specValueArrays);
        
        // 为每个组合找到对应的 SKU ID
        $result = [];
        foreach ($combinations as $combination) {
            $specValueIds = collect($combination)->pluck('specification_value_id')->toArray();
            $variantId = $this->findVariantBySpecificationValues($specValueIds);
            
            // 格式化显示文本
            $displayParts = [];
            foreach ($specs as $spec) {
                $specValues = collect($combination)
                    ->where('specification_id', $spec['id'])
                    ->pluck('specification_value_name')
                    ->toArray();
                if (!empty($specValues)) {
                    $displayParts[] = $spec['name'] . ': ' . implode(', ', $specValues);
                }
            }
            
            $result[] = [
                'variant_id' => $variantId,
                'specification_values' => $combination,
                'display' => implode('; ', $displayParts),
            ];
        }
        
        return $result;
    }

    public function mount(string $slug): void
    {
        $localeCurrencyService = app(LocaleCurrencyService::class);
        $lang = $localeCurrencyService->getLanguageByCode(session('lang'));

        $this->product = Product::with([
            'media',
            'productTranslations',
            'productVariants.specificationValues.specificationValueTranslations',
            'productVariants.specificationValues.specification.specificationTranslations',
            'productVariants' => fn ($q) => $q->orderBy('price'),
            'productVariants.media',
            'productCategories.categoryTranslations',
            'attributeValues.attributeValueTranslations',
            'attributeValues.attribute.attributeTranslations',
        ])->active()->where('slug', $slug)->firstOrFail();

        $this->variants = $this->product->productVariants;

        // 初始化已选择的规格值
        $this->selectedOptions = [];

        // 默认选第一个 SKU
        $firstVariant = $this->variants->first();
        if ($firstVariant) {
            $this->selectedVariantId = $firstVariant->id;
            // 根据第一个 SKU 初始化已选择的规格值
            foreach ($firstVariant->specificationValues as $sv) {
                $specId = (int)($sv->pivot->specification_id ?? $sv->specification_id);
                $this->selectedOptions[$specId] = $sv->id;
            }
        }

        // 获取可用促销信息
        $this->updateAvailablePromotions();

        // 分类名（多语言）
        $this->categoryNames = $this->getCategoryNames($lang);
    }

    /**
     * 选择/取消选择规格值
     */
    public function toggleSpecificationValue(int $specId, int $valueId): void
    {
        // 如果已选择，则取消选择
        if (isset($this->selectedOptions[$specId]) && $this->selectedOptions[$specId] == $valueId) {
            unset($this->selectedOptions[$specId]);
        } else {
            // 选择新的规格值（同一规格只能选一个）
            $this->selectedOptions[$specId] = $valueId;
        }
        
        // 根据已选择的规格值匹配 SKU
        $this->matchVariantFromSelectedOptions();
    }

    /**
     * 根据已选择的规格值匹配对应的 SKU
     */
    protected function matchVariantFromSelectedOptions(): void
    {
        // 如果所有规格都已选择，尝试匹配唯一 SKU
        $specs = $this->specificationsForSelection;
        if (count($this->selectedOptions) === count($specs) && count($specs) > 0) {
            $selectedValueIds = array_values($this->selectedOptions);
            $variantId = $this->findVariantBySpecificationValues($selectedValueIds);
            
            if ($variantId) {
                $this->selectedVariantId = $variantId;
                $this->updateAvailablePromotions();
                $this->qty = 1;
            } else {
                $this->selectedVariantId = null;
            }
        } else {
            // 如果规格未全部选择，清空选中的 SKU
            $this->selectedVariantId = null;
        }
    }

    /**
     * 检查规格值是否可选（在当前已选组合下是否有对应的 SKU）
     */
    public function isSpecificationValueAvailable(int $specId, int $valueId): bool
    {
        // 如果当前规格已选择其他值，这个值不可选（同一规格只能选一个）
        if (isset($this->selectedOptions[$specId]) && $this->selectedOptions[$specId] != $valueId) {
            return false;
        }
        
        // 构建测试组合（当前已选 + 要测试的规格值）
        $testOptions = $this->selectedOptions;
        $testOptions[$specId] = $valueId;
        
        // 检查是否有 SKU 匹配这个组合（部分匹配即可，不需要全部规格都选择）
        $selectedValueIds = array_values($testOptions);
        
        return $this->variantService->hasMatchingVariant($this->variants, $selectedValueIds);
    }

    /**
     * 检查规格值是否已选
     */
    public function isSpecificationValueSelected(int $specId, int $valueId): bool
    {
        return isset($this->selectedOptions[$specId]) && $this->selectedOptions[$specId] == $valueId;
    }

    /**
     * 获取规格值的状态：'available', 'selected', 'disabled'
     */
    public function getSpecificationValueStatus(int $specId, int $valueId): string
    {
        if ($this->isSpecificationValueSelected($specId, $valueId)) {
            return 'selected';
        }
        
        if ($this->isSpecificationValueAvailable($specId, $valueId)) {
            return 'available';
        }
        
        return 'disabled';
    }

    /**
     * 获取规格值的图片 URL（如果有）
     */
    public function getSpecificationValueImage(int $valueId): ?string
    {
        // 目前 SpecificationValue 模型没有图片字段
        // 如果需要支持图片规格值，可以在 SpecificationValue 模型中添加 media 关系
        // 然后在这里实现获取逻辑
        return null;
    }

    public function selectVariant(int $variantId): void
    {
        $this->selectedVariantId = $variantId;
        $this->updateAvailablePromotions();
        $this->qty = 1;
        
        // 根据选中的 SKU 更新已选择的规格值
        $variant = $this->variants->firstWhere('id', $variantId);
        if ($variant) {
            $this->selectedOptions = [];
            foreach ($variant->specificationValues as $sv) {
                $specId = (int)($sv->pivot->specification_id ?? $sv->specification_id);
                $this->selectedOptions[$specId] = $sv->id;
            }
        }
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
        $productAttributes = $this->product->attributeValues ?? collect();
        $maxQty = $variant?->stock ?? 1;

        // 准备结构化数据
        $structuredData = $this->buildStructuredData($name, $shortDesc, $images, $variant, $currencyCode, $price, $productAttributes, $lang);

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
            'productAttributes' => $productAttributes,
            'maxQty' => $maxQty,
            'structuredData' => $structuredData,
            'lang' => $lang,
            'currencyCode' => $currencyCode,
            'currencyService' => $localeCurrencyService,
            'specificationsForSelection' => $this->specificationsForSelection,
            'skuCombinations' => $this->skuCombinations,
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
     * 获取产品变体规格字符串（只显示后台 SKU 管理中已选择的规格）
     */
    protected function getProductVariantSpecs($productVariant, $lang): string
    {
        if (!$productVariant) {
            return '';
        }
        
        // 获取该商品所有 SKU 使用的规格 ID（从 pivot 表中获取）
        // 缓存结果，避免重复计算
        static $usedSpecificationIdsCache = null;
        if ($usedSpecificationIdsCache === null) {
            $usedSpecificationIdsCache = $this->product->productVariants
                ->flatMap(function ($variant) {
                    return $variant->specificationValues->map(function ($sv) {
                        // 从 pivot 中获取 specification_id
                        return (int)($sv->pivot->specification_id ?? $sv->specification_id);
                    });
                })
                ->unique()
                ->values()
                ->toArray();
        }
        
        // 只显示已使用的规格的规格值，并按规格分组
        $specGroups = [];
        foreach ($productVariant->specificationValues as $sv) {
            // 从 pivot 中获取 specification_id
            $specId = (int)($sv->pivot->specification_id ?? $sv->specification_id);
            
            // 只处理在后台 SKU 管理中已选择的规格
            if (!in_array($specId, $usedSpecificationIdsCache)) {
                continue;
            }
            
            // 获取规格名称（如果已加载关系）
            $spec = $sv->specification;
            if (!$spec) {
                $spec = $sv->specification()->with('specificationTranslations')->first();
            }
            $specTrans = $spec?->specificationTranslations
                ->where('language_id', $lang?->id)
                ->first();
            $specName = $specTrans && $specTrans->name 
                ? $specTrans->name 
                : ($spec?->specificationTranslations->first()->name ?? $specId);
            
            // 获取规格值名称
            $valueTrans = $sv->specificationValueTranslations
                ->where('language_id', $lang?->id)
                ->first();
            $valueName = $valueTrans && $valueTrans->name 
                ? $valueTrans->name 
                : ($sv->specificationValueTranslations->first()->name ?? $sv->id);
            
            // 按规格分组
            if (!isset($specGroups[$specId])) {
                $specGroups[$specId] = [
                    'name' => $specName,
                    'values' => [],
                ];
            }
            $specGroups[$specId]['values'][] = $valueName;
        }
        
        // 按规格 ID 排序，然后格式化为字符串（像后台表格一样：规格名: 值1, 值2）
        ksort($specGroups);
        $parts = [];
        foreach ($specGroups as $specGroup) {
            $parts[] = $specGroup['name'] . ': ' . implode(', ', $specGroup['values']);
        }
        
        return implode('; ', $parts);
    }

    /**
     * 获取属性显示名称
     */
    protected function getAttributeDisplayNames($attrValue, $lang): array
    {
        $attrTrans = $attrValue->attribute->attributeTranslations
            ->where('language_id', $lang?->id)
            ->first();
        $attrValueTrans = $attrValue->attributeValueTranslations
            ->where('language_id', $lang?->id)
            ->first();

        $attrName = $attrTrans && $attrTrans->name ? $attrTrans->name : $attrValue->attribute->id;
        $attrValueName = $attrValueTrans && $attrValueTrans->name ? $attrValueTrans->name : $attrValue->id;

        return [
            'attrName' => $attrName,
            'attrValueName' => $attrValueName,
        ];
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
