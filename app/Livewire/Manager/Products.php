<?php

namespace App\Livewire\Manager;

use App\Enums\ProductStatusEnum;
use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Products extends Component
{
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public array $filterStatus = [];
    public array $filterTranslationStatus = [];
    public ?int $filterCategoryId = null;
    public bool $filterLowStock = false;
    public bool $filterOutOfStock = false;

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTranslationStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterLowStock(): void
    {
        $this->resetPage();
    }

    public function updatingFilterOutOfStock(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterStatus = [];
        $this->filterTranslationStatus = [];
        $this->filterCategoryId = null;
        $this->filterLowStock = false;
        $this->filterOutOfStock = false;
        $this->resetPage();
    }

    public function deleteProduct(int $id): void
    {
        $this->deleteModel(Product::class, $id);
    }

    #[Computed]
    public function products()
    {
        $lang = $this->getCurrentLanguage();
        $currentCurrencyCode = $this->getCurrentCurrencyCode();

        $query = Product::query()
            ->with([
                'productCategories.categoryTranslations',
                'productVariants',
                'productTranslations',
                'media',
            ]);

        // 搜索：按商品翻译名称
        if ($this->search !== '') {
            $search = $this->search;
            $query->whereHas('productTranslations', function (Builder $q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        // 状态筛选
        if (! empty($this->filterStatus)) {
            $query->whereIn('status', $this->filterStatus);
        }

        // 翻译状态筛选
        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        // 分类筛选
        if ($this->filterCategoryId) {
            $categoryId = $this->filterCategoryId;
            $query->whereHas('productCategories', function (Builder $q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // 低库存 / 无库存
        if ($this->filterLowStock && ! $this->filterOutOfStock) {
            $query->whereIn('id', function ($subQuery) {
                $subQuery->select('product_id')
                    ->from('product_variants')
                    ->groupBy('product_id')
                    ->havingRaw('SUM(stock) <= 10');
            });
        } elseif ($this->filterOutOfStock && ! $this->filterLowStock) {
            $query->whereIn('id', function ($subQuery) {
                $subQuery->select('product_id')
                    ->from('product_variants')
                    ->groupBy('product_id')
                    ->havingRaw('SUM(stock) = 0');
            });
        }

        // 排序：创建时间倒序
        $paginator = $query->orderByDesc('created_at')->paginate(15);

        // 预计算一些展示字段（价格区间、库存）
        $service = $this->getLocaleService();
        $paginator->getCollection()->transform(function (Product $product) use ($service, $currentCurrencyCode, $lang) {
            $variants = $product->productVariants;
            if ($variants->isEmpty()) {
                $product->price_range_text = '-';
                $product->total_stock = 0;
            } else {
                $prices = $variants->pluck('price')->filter()->sort()->values();
                if ($prices->isEmpty()) {
                    $product->price_range_text = '-';
                } elseif ($prices->count() === 1) {
                    $product->price_range_text = $service->convertWithSymbol($prices->first(), $currentCurrencyCode);
                } else {
                    $min = $service->convertWithSymbol($prices->first(), $currentCurrencyCode);
                    $max = $service->convertWithSymbol($prices->last(), $currentCurrencyCode);
                    $product->price_range_text = "{$min} - {$max}";
                }
                $product->total_stock = $variants->sum('stock');
            }

            // 分类名称
            $names = [];
            foreach ($product->productCategories as $cat) {
                $names[] = $this->translatedField($cat->categoryTranslations, $lang, 'name', '');
            }
            $product->category_names_text = implode(', ', array_filter($names)) ?: '-';

            return $product;
        });

        return $paginator;
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();

        // 分类选项（用于筛选）
        $categories = \App\Models\Category::with('categoryTranslations')->get()->map(function ($cat) use ($lang) {
            return [
                'id' => $cat->id,
                'name' => $this->translatedField($cat->categoryTranslations, $lang, 'name', (string) $cat->id),
            ];
        });

        return view('livewire.manager.products', [
            'products' => $this->products,
            'categories' => $categories,
            'statusOptions' => ProductStatusEnum::options(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
