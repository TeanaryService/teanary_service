<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Category;
use App\Services\LocaleCurrencyService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Categories extends Component
{
    use WithPagination;

    public $search = '';
    public $parentIdFilter = null;
    public $translationStatusFilter = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'parentIdFilter' => ['except' => null],
        'translationStatusFilter' => ['except' => []],
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
        $this->parentIdFilter = null;
        $this->translationStatusFilter = [];
        $this->resetPage();
    }

    public function getCategoriesProperty()
    {
        $locale = app()->getLocale();
        $lang = $this->localeService->getLanguageByCode($locale);

        $query = Category::query()
            ->with(['category.categoryTranslations', 'categoryTranslations'])
            ->withCount('products')
            ->when($this->search, function (Builder $query) {
                $query->whereHas('categoryTranslations', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->parentIdFilter, function (Builder $query) {
                $query->where('parent_id', $this->parentIdFilter);
            })
            ->when($this->translationStatusFilter, function (Builder $query) {
                $query->whereIn('translation_status', $this->translationStatusFilter);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function getParentCategoriesProperty()
    {
        $locale = app()->getLocale();
        $lang = $this->localeService->getLanguageByCode($locale);

        return Category::with('categoryTranslations')
            ->whereNull('parent_id')
            ->get()
            ->map(function ($cat) use ($lang) {
                $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
                $cat->display_name = $translation?->name ?? $cat->categoryTranslations->first()?->name ?? $cat->slug;
                return $cat;
            })
            ->sortBy('display_name');
    }

    public function getTranslationStatusOptionsProperty(): array
    {
        return TranslationStatusEnum::options();
    }

    public function render()
    {
        return view('livewire.manager.categories', [
            'categories' => $this->categories,
            'parentCategories' => $this->parentCategories,
            'translationStatusOptions' => $this->translationStatusOptions,
        ])->layout('components.layouts.manager');
    }
}
