<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Category;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Categories extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filterParentId = null;
    public array $filterTranslationStatus = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterParentId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTranslationStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterParentId = null;
        $this->filterTranslationStatus = [];
        $this->resetPage();
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->delete();
        Cache::forget(\App\Support\CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
        session()->flash('message', __('app.deleted_successfully'));
    }

    #[Computed]
    public function categories()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = Category::query()
            ->with(['category.categoryTranslations', 'categoryTranslations', 'products']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $query->whereHas('categoryTranslations', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%');
            });
        }

        // 筛选：父分类
        if ($this->filterParentId !== null) {
            if ($this->filterParentId === 0) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $this->filterParentId);
            }
        }

        // 筛选：翻译状态
        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getCategoryName($category, $lang)
    {
        $translation = $category->categoryTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $category->categoryTranslations->first();

        return $first ? $first->name : __('manager.category.unnamed');
    }

    public function getParentName($parent, $lang)
    {
        if (! $parent) {
            return __('manager.category.root');
        }
        $translation = $parent->categoryTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $parent->categoryTranslations->first();

        return $first ? $first->name : $parent->slug;
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $parentCategories = Category::with('categoryTranslations')
            ->whereNull('parent_id')
            ->get();

        return view('livewire.manager.categories', [
            'categories' => $this->categories,
            'lang' => $lang,
            'parentCategories' => $parentCategories,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
