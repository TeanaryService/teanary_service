<?php

namespace App\Livewire\Manager;

use App\Enums\ProductStatusEnum;
use App\Enums\TranslationStatusEnum;
use App\Models\Product;
use App\Services\LocaleCurrencyService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use WithPagination;

    public string $search = '';
    public array $filterStatus = [];
    public array $filterTranslationStatus = [];
    public ?int $filterCategoryId = null;
    public bool $filterLowStock = false;
    public bool $filterOutOfStock = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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
        $product = Product::findOrFail($id);
        $product->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    #[Computed]
    public function products()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();

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
        $paginator->getCollection()->transform(function (Product $product) use ($service, $currentCurrencyCode) {
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
            $locale = app()->getLocale();
            $lang = $service->getLanguageByCode($locale);
            $names = [];
            foreach ($product->productCategories as $cat) {
                $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
                $names[] = $translation && $translation->name
                    ? $translation->name
                    : ($cat->categoryTranslations->first()->name ?? '');
            }
            $product->category_names_text = implode(', ', array_filter($names)) ?: '-';

            return $product;
        });

        return $paginator;
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        // 分类选项（用于筛选）
        $categories = \App\Models\Category::with('categoryTranslations')->get()->map(function ($cat) use ($lang) {
            $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();

            return [
                'id' => $cat->id,
                'name' => $translation && $translation->name
                    ? $translation->name
                    : ($cat->categoryTranslations->first()->name ?? $cat->id),
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
