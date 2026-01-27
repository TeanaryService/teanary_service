<?php

namespace App\Livewire\Manager\Components;

use App\Enums\ProductStatusEnum;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Specification;
use App\Models\SpecificationValue;
use App\Services\LocaleCurrencyService;
use App\Services\ProductVariantService;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageProductVariants extends Component
{
    use HasTranslatedNames;
    use UsesLocaleCurrency;
    use WithFileUploads;

    public int $productId;
    public Product $product;

    /**
     * 已选规格及规格值：
     * [spec_id => [value_id1, value_id2, ...]].
     */
    public array $selectedSpecifications = [];

    /**
     * SKU 行数据：
     * [
     *   [
     *     'id' => ?int,
     *     'specification_values' => [['specification_id'=>, 'specification_value_id'=>], ...],
     *     'sku' => '',
     *     'price' => '',
     *     'cost' => '',
     *     'stock' => 0,
     *     'status' => 'active',
     *     'image' => UploadedFile|null,
     *     'image_url' => string|null,
     *   ],
     *   ...
     * ].
     */
    public array $skus = [];

    /**
     * 被手动删除（排除）的规格组合 key。
     * 用于支持：在不改变规格选择（笛卡尔积不变）的情况下，删除某一行 SKU 后不要被 generateSkus() 重新生成出来。
     *
     * @var string[]
     */
    public array $excludedSkuKeys = [];

    public string $bulkPrice = '';
    public string $bulkCost = '';
    public string $bulkStock = '';
    public string $bulkStatus = 'active';
    public bool $showBulkActions = false;
    public array $selectedSkus = [];

    protected ?LocaleCurrencyService $localeCurrencyService = null;
    protected ?ProductVariantService $variantService = null;
    public ?string $currencySymbol = null;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
        $this->product = Product::with(['productVariants.specificationValues', 'productVariants.media'])->findOrFail($productId);

        $currency = $this->getLocaleService()->getCurrencyByCode(session('currency'));
        $this->currencySymbol = $currency->symbol ?? '';
        $this->variantService = app(ProductVariantService::class);

        $this->loadExistingVariants();
    }

    /**
     * 加载现有 SKU，同步 selectedSpecifications 与 skus.
     */
    protected function loadExistingVariants(): void
    {
        // 从数据库重新加载时，清空排除列表（以数据库为准）
        $this->excludedSkuKeys = [];

        $this->selectedSpecifications = [];
        $this->skus = [];

        $variants = $this->product->productVariants()->with(['specificationValues', 'media'])->get();

        foreach ($variants as $variant) {
            $specValues = [];
            foreach ($variant->specificationValues as $sv) {
                $specId = (string) ($sv->pivot->specification_id ?? $sv->specification_id);
                $this->selectedSpecifications[$specId] ??= [];
                if (! in_array($sv->id, $this->selectedSpecifications[$specId], true)) {
                    $this->selectedSpecifications[$specId][] = (int) $sv->id;
                }
                $specValues[] = [
                    'specification_id' => (int) $specId,
                    'specification_value_id' => (int) $sv->id,
                ];
            }

            usort($specValues, fn ($a, $b) => $a['specification_id'] <=> $b['specification_id']);

            $imageUrl = null;
            $media = $variant->getFirstMedia('image');
            if ($media) {
                $imageUrl = $media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : $media->getUrl();
            }

            $this->skus[] = [
                'id' => $variant->id,
                'specification_values' => $specValues,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'cost' => $variant->cost,
                'stock' => $variant->stock,
                'status' => $this->product->status->value ?? ProductStatusEnum::Active->value,
                'image' => null,
                'image_url' => $imageUrl,
            ];
        }

        if (empty($this->skus)) {
            $this->generateSkus();
        }
    }

    public function updatedSelectedSpecifications($value, $key): void
    {
        if (! is_array($value)) {
            $this->selectedSpecifications[$key] = [];
            $this->generateSkus();

            return;
        }

        $filtered = array_values(array_map(
            fn ($v) => (int) $v,
            array_filter($value, fn ($v) => $v !== null && $v !== '')
        ));

        $this->selectedSpecifications[$key] = $filtered;
        $this->generateSkus();
    }

    /**
     * 切换某个规格值的选中状态（供视图直接调用）.
     */
    public function toggleSpecificationValue(int $specId, int $valueId): void
    {
        $key = (string) $specId;
        $valueId = (int) $valueId;

        if (! isset($this->selectedSpecifications[$key]) || ! is_array($this->selectedSpecifications[$key])) {
            $this->selectedSpecifications[$key] = [];
        }

        $index = array_search($valueId, $this->selectedSpecifications[$key], true);
        if ($index !== false) {
            unset($this->selectedSpecifications[$key][$index]);
            $this->selectedSpecifications[$key] = array_values($this->selectedSpecifications[$key]);
        } else {
            $this->selectedSpecifications[$key][] = $valueId;
        }

        $this->generateSkus();
    }

    /**
     * 生成规格笛卡尔积 → SKU 行.
     */
    public function generateSkus(): void
    {
        $specIds = array_keys($this->selectedSpecifications);
        usort($specIds, fn ($a, $b) => (int) $a <=> (int) $b);

        if (empty($specIds)) {
            $this->skus = [];

            return;
        }

        $specValueArrays = [];
        foreach ($specIds as $specId) {
            $valueIds = $this->selectedSpecifications[$specId] ?? [];
            if (! is_array($valueIds) || empty($valueIds)) {
                continue;
            }
            sort($valueIds);
            $specValueArrays[] = array_map(fn ($valueId) => [
                'specification_id' => (int) $specId,
                'specification_value_id' => (int) $valueId,
            ], $valueIds);
        }

        if (empty($specValueArrays)) {
            $this->skus = [];

            return;
        }

        if (! $this->variantService) {
            $this->variantService = app(ProductVariantService::class);
        }

        $combinations = $this->variantService->cartesianProduct($specValueArrays);

        $existingByKey = [];
        foreach ($this->skus as $sku) {
            $key = $this->buildSpecValuesKey($sku['specification_values']);
            $existingByKey[$key] = $sku;
        }

        $newSkus = [];
        foreach ($combinations as $combination) {
            $key = $this->buildSpecValuesKey($combination);
            if (in_array($key, $this->excludedSkuKeys, true)) {
                continue;
            }
            if (isset($existingByKey[$key])) {
                $newSkus[] = $existingByKey[$key];
            } else {
                $newSkus[] = [
                    'id' => null,
                    'specification_values' => $combination,
                    'sku' => '',
                    'price' => '',
                    'cost' => '',
                    'stock' => 0,
                    'status' => $this->product->status->value ?? ProductStatusEnum::Active->value,
                    'image' => null,
                    'image_url' => null,
                ];
            }
        }

        $this->skus = $newSkus;
    }

    /**
     * 重新生成 SKU：清空“排除列表”，恢复全部组合。
     */
    public function regenerateSkus(): void
    {
        $this->excludedSkuKeys = [];
        $this->generateSkus();
    }

    protected function buildSpecValuesKey(array $specValues): string
    {
        $parts = collect($specValues)
            ->map(fn ($sv) => (int) $sv['specification_id'].':'.(int) $sv['specification_value_id'])
            ->sort()
            ->values()
            ->all();

        return implode(',', $parts);
    }

    public function updatedSkus($value, $key): void
    {
        if (str_ends_with($key, '.image') && $value) {
            $parts = explode('.', $key);
            // $key 形如 "3.image"，第一段才是数组索引
            $index = (int) ($parts[0] ?? 0);
            if (isset($this->skus[$index]) && $value) {
                try {
                    $this->skus[$index]['image_url'] = $value->temporaryUrl();
                } catch (\Throwable) {
                    $this->skus[$index]['image_url'] = null;
                }
            }
        }
    }

    public function removeSkuImage(int $index): void
    {
        if (! isset($this->skus[$index])) {
            return;
        }

        $row = $this->skus[$index];

        // 已保存到数据库：清空媒体库
        if (! empty($row['id'])) {
            $variant = ProductVariant::find((int) $row['id']);
            if ($variant) {
                $variant->clearMediaCollection('image');
            }
        }

        // 清空当前行临时上传与预览
        $this->skus[$index]['image'] = null;
        $this->skus[$index]['image_url'] = null;
    }

    public function applyBulkUpdate(): void
    {
        foreach ($this->selectedSkus as $index) {
            if (! isset($this->skus[$index])) {
                continue;
            }
            if ($this->bulkPrice !== '') {
                $this->skus[$index]['price'] = $this->bulkPrice;
            }
            if ($this->bulkCost !== '') {
                $this->skus[$index]['cost'] = $this->bulkCost;
            }
            if ($this->bulkStock !== '') {
                $this->skus[$index]['stock'] = $this->bulkStock;
            }
            if ($this->bulkStatus !== '') {
                $this->skus[$index]['status'] = $this->bulkStatus;
            }
        }

        $this->resetBulkActions();
    }

    public function resetBulkActions(): void
    {
        $this->bulkPrice = '';
        $this->bulkCost = '';
        $this->bulkStock = '';
        $this->bulkStatus = ProductStatusEnum::Active->value;
        $this->selectedSkus = [];
        $this->showBulkActions = false;
    }

    public function saveAll(): void
    {
        foreach ($this->skus as $sku) {
            if (empty($sku['sku'])) {
                $this->dispatch('flash-message', type: 'error', message: __('manager.product_variants.manage.sku_code_required'));

                return;
            }
        }

        $skuCodes = array_column($this->skus, 'sku');
        if (count($skuCodes) !== count(array_unique($skuCodes))) {
            $this->dispatch('flash-message', type: 'error', message: __('manager.product_variants.manage.sku_code_unique'));

            return;
        }

        $existingSkus = ProductVariant::where('product_id', '!=', $this->productId)
            ->whereIn('sku', $skuCodes)
            ->pluck('sku')
            ->toArray();
        if (! empty($existingSkus)) {
            $this->dispatch('flash-message', type: 'error', message: __('manager.product_variants.manage.sku_code_exists').implode(', ', $existingSkus));

            return;
        }

        $existingVariants = ProductVariant::where('product_id', $this->productId)
            ->with(['specificationValues', 'media'])
            ->get();

        // 先把旧变体图片复制到临时目录（因为删除旧变体会级联删除媒体文件，原路径会失效）
        $tmpDir = storage_path('app/tmp/variant-images');
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $imageMap = []; // specKey => tmpFilePath
        $tmpFiles = [];
        foreach ($existingVariants as $variant) {
            $key = $this->buildSpecValuesKey(
                $variant->specificationValues->map(fn (SpecificationValue $sv) => [
                    'specification_id' => (int) ($sv->pivot->specification_id ?? $sv->specification_id),
                    'specification_value_id' => (int) $sv->id,
                ])->all()
            );
            $media = $variant->getFirstMedia('image');
            if ($media) {
                $path = $media->getPath();
                if ($path && file_exists($path)) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
                    $tmpPath = rtrim($tmpDir, '/').'/'.Str::uuid().'.'.$ext;
                    if (@copy($path, $tmpPath) && file_exists($tmpPath)) {
                        $imageMap[$key] = $tmpPath;
                        $tmpFiles[] = $tmpPath;
                    }
                }
            }
        }

        try {
            foreach ($existingVariants as $variant) {
                $variant->delete();
            }

            foreach ($this->skus as $row) {
                $pivotData = [];
                foreach ($row['specification_values'] as $sv) {
                    $pivotData[$sv['specification_value_id']] = [
                        'specification_id' => $sv['specification_id'],
                    ];
                }

                $variant = ProductVariant::create([
                    'product_id' => $this->productId,
                    'sku' => $row['sku'],
                    'price' => $row['price'] ?: null,
                    'cost' => $row['cost'] ?: null,
                    'stock' => $row['stock'] ?? 0,
                ]);
                $variant->syncSpecificationValues($pivotData);

                $key = $this->buildSpecValuesKey($row['specification_values']);
                if (! empty($row['image'])) {
                    $variant->addMedia($row['image']->getRealPath())
                        ->usingFileName($row['image']->getClientOriginalName())
                        ->toMediaCollection('image');
                } elseif (isset($imageMap[$key]) && file_exists($imageMap[$key])) {
                    $variant->addMedia($imageMap[$key])->preservingOriginal()->toMediaCollection('image');
                }
            }
        } finally {
            // 清理临时文件
            foreach ($tmpFiles as $tmp) {
                if (is_string($tmp) && file_exists($tmp)) {
                    @unlink($tmp);
                }
            }
        }

        $this->product->refresh();
        $this->product->load(['productVariants.specificationValues', 'productVariants.media']);
        $this->loadExistingVariants();

        $this->dispatch('flash-message', type: 'success', message: __('manager.product_variants.manage.save_success'));
    }

    /**
     * 删除单个变体.
     */
    public function deleteVariant(int $index): void
    {
        if (! isset($this->skus[$index])) {
            return;
        }

        $sku = $this->skus[$index];
        $specKey = $this->buildSpecValuesKey($sku['specification_values'] ?? []);
        if ($specKey !== '' && ! in_array($specKey, $this->excludedSkuKeys, true)) {
            $this->excludedSkuKeys[] = $specKey;
        }

        // 如果变体已保存到数据库，则从数据库删除
        if (! empty($sku['id'])) {
            $variant = ProductVariant::find($sku['id']);
            if ($variant) {
                $variant->delete();
            }
        }

        // 从数组中移除（不做重排、不重新生成笛卡尔积）
        // 否则会触发整表重算/DOM 重排，容易出现“整表隐藏、刷新才恢复”的现象。
        unset($this->skus[$index]);

        // 同步批量选择状态（移除被删行的 index）
        if (! empty($this->selectedSkus)) {
            $this->selectedSkus = array_values(array_filter(
                $this->selectedSkus,
                fn ($i) => (int) $i !== (int) $index
            ));
        }

        $this->dispatch('flash-message', type: 'success', message: __('app.deleted_successfully'));
    }

    #[Computed]
    public function specifications()
    {
        $lang = $this->getCurrentLanguage();
        $specs = Specification::with(['specificationTranslations', 'specificationValues.specificationValueTranslations'])
            ->orderBy('id')
            ->get();

        $result = [];
        foreach ($specs as $spec) {
            $specName = $this->translatedField($spec->specificationTranslations, $lang, 'name', (string) $spec->id);

            $values = [];
            foreach ($spec->specificationValues->sortBy('id') as $sv) {
                $valName = $this->translatedField($sv->specificationValueTranslations, $lang, 'name', (string) $sv->id);

                $specIdKey = (string) $spec->id;
                $selectedValues = $this->selectedSpecifications[$specIdKey] ?? [];
                if (! is_array($selectedValues)) {
                    $selectedValues = [];
                }

                $values[] = [
                    'id' => $sv->id,
                    'name' => $valName,
                    'selected' => in_array($sv->id, $selectedValues, true),
                ];
            }

            $result[] = [
                'id' => $spec->id,
                'name' => $specName,
                'values' => $values,
            ];
        }

        return $result;
    }

    public function render()
    {
        return view('livewire.manager.components.manage-product-variants', [
            'specifications' => $this->specifications,
        ]);
    }
}
