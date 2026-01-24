<?php

namespace App\Livewire\Manager;

use App\Enums\ProductStatusEnum;
use App\Enums\TranslationStatusEnum;
use App\Models\Product;
use App\Models\Category;
use App\Services\LocaleCurrencyService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = [];
    public $translationStatusFilter = [];
    public $categoryFilter = null;
    public $lowStockFilter = false;
    public $outOfStockFilter = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => []],
        'translationStatusFilter' => ['except' => []],
        'categoryFilter' => ['except' => null],
        'lowStockFilter' => ['except' => false],
        'outOfStockFilter' => ['except' => false],
    ];

    protected LocaleCurrencyService $localeService;

    public function mount(): void
    {
        $this->localeService = app(LocaleCurrencyService::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = [];
        $this->translationStatusFilter = [];
        $this->categoryFilter = null;
        $this->lowStockFilter = false;
        $this->outOfStockFilter = false;
        $this->resetPage();
    }

    public function getProductsProperty()
    {
        $locale = app()->getLocale();
        $lang = $this->localeService->getLanguageByCode($locale);

        $query = Product::query()
            ->with(['productTranslations', 'productCategories.category.categoryTranslations'])
            ->withCount('productVariants')
            ->when($this->search, function (Builder $query) {
                $query->whereHas('productTranslations', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->whereIn('status', $this->statusFilter);
            })
            ->when($this->translationStatusFilter, function (Builder $query) {
                $query->whereIn('translation_status', $this->translationStatusFilter);
            })
            ->when($this->categoryFilter, function (Builder $query) {
                $query->whereHas('productCategories', function ($q) {
                    $q->where('category_id', $this->categoryFilter);
                });
            })
            ->when($this->lowStockFilter, function (Builder $query) {
                $query->whereIn('id', function ($subQuery) {
                    $subQuery->select('product_id')
                        ->from('product_variants')
                        ->groupBy('product_id')
                        ->havingRaw('SUM(stock) <= 10');
                });
            })
            ->when($this->outOfStockFilter, function (Builder $query) {
                $query->whereIn('id', function ($subQuery) {
                    $subQuery->select('product_id')
                        ->from('product_variants')
                        ->groupBy('product_id')
                        ->havingRaw('SUM(stock) = 0');
                });
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function getCategoriesProperty()
    {
        $locale = app()->getLocale();
        $lang = $this->localeService->getLanguageByCode($locale);

        return Category::with('categoryTranslations')
            ->get()
            ->map(function ($cat) use ($lang) {
                $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
                $cat->display_name = $translation?->name ?? $cat->categoryTranslations->first()?->name ?? $cat->slug;
                return $cat;
            })
            ->sortBy('display_name');
    }

    public function getStatusOptionsProperty(): array
    {
        return ProductStatusEnum::options();
    }

    public function getTranslationStatusOptionsProperty(): array
    {
        return TranslationStatusEnum::options();
    }

    public function render()
    {
        return view('livewire.manager.products', [
            'products' => $this->products,
            'categories' => $this->categories,
            'statusOptions' => $this->statusOptions,
            'translationStatusOptions' => $this->translationStatusOptions,
        ])->layout('components.layouts.manager');
    }
}
