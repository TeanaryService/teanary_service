<?php

namespace App\Livewire\Manager;

use App\Enums\PromotionTypeEnum;
use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Promotion;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Promotions extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public array $filterTypes = [];
    public string $filterActive = '';
    public array $filterTranslationStatus = [];

    public function updatingFilterTypes(): void
    {
        $this->resetPage();
    }

    public function updatingFilterActive(): void
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
        $this->filterTypes = [];
        $this->filterActive = '';
        $this->filterTranslationStatus = [];
        $this->resetPage();
    }

    public function deletePromotion(int $id): void
    {
        $this->deleteModel(Promotion::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->promotions->getCollection();
    }

    public function batchDeletePromotions(): void
    {
        $this->batchDelete(Promotion::class);
    }

    public function batchSetPromotionTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(Promotion::class, $status);
    }

    public function batchSetPromotionActiveStatus(bool $active): void
    {
        $this->batchUpdateActiveStatus(Promotion::class, $active);
    }

    #[Computed]
    public function promotions()
    {
        $lang = $this->getCurrentLanguage();

        $query = Promotion::query()
            ->with(['promotionTranslations']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('promotionTranslations', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        // 筛选：类型
        if (! empty($this->filterTypes)) {
            $query->whereIn('type', $this->filterTypes);
        }

        // 筛选：是否启用
        if ($this->filterActive !== '') {
            $query->where('active', $this->filterActive === '1');
        }

        // 筛选：翻译状态
        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getPromotionName($promotion, $lang)
    {
        return $this->translatedField($promotion->promotionTranslations, $lang, 'name', __('manager.promotion.unnamed'));
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();

        return view('livewire.manager.promotions', [
            'promotions' => $this->promotions,
            'lang' => $lang,
            'typeOptions' => PromotionTypeEnum::options(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
