<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\AttributeValue;
use App\Services\LocaleCurrencyService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AttributeValues extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filterAttributeId = null;
    public array $filterTranslationStatus = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAttributeId(): void
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
        $this->filterAttributeId = null;
        $this->filterTranslationStatus = [];
        $this->resetPage();
    }

    public function deleteAttributeValue(int $id): void
    {
        $attributeValue = AttributeValue::findOrFail($id);
        $attributeValue->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    #[Computed]
    public function attributeValues()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = AttributeValue::query()
            ->with(['attribute.attributeTranslations', 'attributeValueTranslations']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $query->whereHas('attributeValueTranslations', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%');
            });
        }

        // 筛选：属性
        if ($this->filterAttributeId) {
            $query->where('attribute_id', $this->filterAttributeId);
        }

        // 筛选：翻译状态
        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getAttributeValueName($attributeValue, $lang)
    {
        $translation = $attributeValue->attributeValueTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $attributeValue->attributeValueTranslations->first();

        return $first ? $first->name : __('manager.attribute_value.unnamed');
    }

    public function getAttributeName($attribute, $lang)
    {
        if (! $attribute) {
            return null;
        }
        $translation = $attribute->attributeTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $attribute->attributeTranslations->first();

        return $first ? $first->name : $attribute->id;
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $attributes = \App\Models\Attribute::with('attributeTranslations')->get();

        return view('livewire.manager.attribute-values', [
            'attributeValues' => $this->attributeValues,
            'lang' => $lang,
            'attributes' => $attributes,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
