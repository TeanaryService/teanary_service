<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\AttributeValue;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AttributeValues extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $filterAttributeId = null;
    public array $filterTranslationStatus = [];

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
        $this->deleteModel(AttributeValue::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->attributeValues->getCollection();
    }

    public function batchDeleteAttributeValues(): void
    {
        $this->batchDelete(AttributeValue::class);
    }

    public function batchSetAttributeValueTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(AttributeValue::class, $status);
    }

    #[Computed]
    public function attributeValues()
    {
        $lang = $this->getCurrentLanguage();

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
        return $this->translatedField($attributeValue->attributeValueTranslations, $lang, 'name', __('manager.attribute_value.unnamed'));
    }

    public function getAttributeName($attribute, $lang)
    {
        if (! $attribute) {
            return null;
        }

        return $this->translatedField($attribute->attributeTranslations, $lang, 'name', (string) $attribute->id);
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();
        $attributes = \App\Models\Attribute::with('attributeTranslations')->get();

        return view('livewire.manager.attribute-values', [
            'attributeValues' => $this->attributeValues,
            'lang' => $lang,
            'attributes' => $attributes,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
