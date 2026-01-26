<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Category;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Categories extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $filterParentId = null;
    public ?string $filterTranslationStatus = null;

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
        $this->filterTranslationStatus = null;
        $this->resetPage();
    }

    public function deleteCategory(int $id): void
    {
        $this->deleteModel(Category::class, $id, CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
    }

    protected function getCurrentPageItems()
    {
        return $this->categories->getCollection();
    }

    public function batchDeleteCategories(): void
    {
        $this->batchDelete(Category::class, CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
    }

    public function batchSetCategoryTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(Category::class, $status, CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
    }

    #[Computed]
    public function categories()
    {
        $lang = $this->getCurrentLanguage();

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
        if ($this->filterTranslationStatus) {
            $query->where('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getCategoryName($category, $lang)
    {
        return $this->translatedField($category->categoryTranslations, $lang, 'name', __('manager.category.unnamed'));
    }

    public function getParentName($parent, $lang)
    {
        if (! $parent) {
            return __('manager.category.root');
        }

        return $this->translatedField($parent->categoryTranslations, $lang, 'name', $parent->slug);
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();
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
