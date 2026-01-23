<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Attribute;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Attributes extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasNavigationRedirect;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public string $filterIsFilterable = '';
    public array $filterTranslationStatus = [];

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
        $this->deleteModel(Attribute::class, $id, 'attributes.with.translations');
    }

    public function toggleFilterable(int $id): void
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->is_filterable = ! $attribute->is_filterable;
        $attribute->save();
        Cache::forget('attributes.with.translations');
        session()->flash('message', $attribute->is_filterable ? __('manager.attribute.filterable') : __('manager.attribute.not_filterable'));
    }

    protected function getCurrentPageItems()
    {
        return $this->attributeList->getCollection();
    }

    public function batchDeleteAttributes(): void
    {
        $this->batchDelete(Attribute::class, 'attributes.with.translations');
    }

    public function batchSetAttributeTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(Attribute::class, $status, 'attributes.with.translations');
    }

    // 使用自定义名称避免与 Livewire 内部 $attributes 属性冲突
    #[Computed]
    public function attributeList()
    {
        $lang = $this->getCurrentLanguage();

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
        return $this->translatedField($attribute->attributeTranslations, $lang, 'name', __('manager.attribute.unnamed'));
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();

        return view('livewire.manager.attributes', [
            'attributes' => $this->attributeList,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
