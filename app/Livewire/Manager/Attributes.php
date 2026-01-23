<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Attribute;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Attributes extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterIsFilterable = '';
    public array $filterTranslationStatus = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterIsFilterable(): void
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
        $this->filterIsFilterable = '';
        $this->filterTranslationStatus = [];
        $this->resetPage();
    }

    public function deleteAttribute(int $id): void
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();
        Cache::forget('attributes.with.translations');
        session()->flash('message', __('app.deleted_successfully'));
    }

    public function toggleFilterable(int $id): void
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->is_filterable = ! $attribute->is_filterable;
        $attribute->save();
        Cache::forget('attributes.with.translations');
        session()->flash('message', $attribute->is_filterable ? __('manager.attribute.filterable') : __('manager.attribute.not_filterable'));
    }

    // 使用自定义名称避免与 Livewire 内部 $attributes 属性冲突
    #[Computed]
    public function attributeList()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = Attribute::query()
            ->with(['attributeTranslations', 'attributeValues']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $query->whereHas('attributeTranslations', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%');
            });
        }

        // 筛选：是否可筛选
        if ($this->filterIsFilterable !== '') {
            $query->where('is_filterable', $this->filterIsFilterable === '1');
        }

        // 筛选：翻译状态
        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getAttributeName($attribute, $lang)
    {
        $translation = $attribute->attributeTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $attribute->attributeTranslations->first();

        return $first ? $first->name : __('manager.attribute.unnamed');
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return view('livewire.manager.attributes', [
            'attributes' => $this->attributeList,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
