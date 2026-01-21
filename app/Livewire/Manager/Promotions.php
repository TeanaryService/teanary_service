<?php

namespace App\Livewire\Manager;

use App\Enums\PromotionTypeEnum;
use App\Enums\TranslationStatusEnum;
use App\Models\Promotion;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

class Promotions extends Component
{
    use WithPagination;

    public string $search = '';
    public array $filterTypes = [];
    public string $filterActive = '';
    public array $filterTranslationStatus = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    public function getPromotionsProperty()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = Promotion::query()
            ->with(['promotionTranslations']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('promotionTranslations', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
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
        $translation = $promotion->promotionTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $promotion->promotionTranslations->first();
        return $first ? $first->name : __('filament.promotion.unnamed');
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return view('livewire.manager.promotions', [
            'promotions' => $this->promotions,
            'lang' => $lang,
            'typeOptions' => PromotionTypeEnum::options(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

