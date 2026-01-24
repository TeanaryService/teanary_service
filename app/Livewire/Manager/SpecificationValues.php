<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\SpecificationValue;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SpecificationValues extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $filterSpecificationId = null;
    public array $filterTranslationStatus = [];

    public function updatingFilterSpecificationId(): void
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
        $this->filterSpecificationId = null;
        $this->filterTranslationStatus = [];
        $this->resetPage();
    }

    public function deleteSpecificationValue(int $id): void
    {
        $this->deleteModel(SpecificationValue::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->specificationValues->getCollection();
    }

    public function batchDeleteSpecificationValues(): void
    {
        $this->batchDelete(SpecificationValue::class);
    }

    public function batchSetSpecificationValueTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(SpecificationValue::class, $status);
    }

    #[Computed]
    public function specificationValues()
    {
        $lang = $this->getCurrentLanguage();

        $query = SpecificationValue::query()
            ->with(['specification.specificationTranslations', 'specificationValueTranslations']);

        if ($this->search) {
            $query->whereHas('specificationValueTranslations', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterSpecificationId) {
            $query->where('specification_id', $this->filterSpecificationId);
        }

        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getSpecificationValueName($value, $lang)
    {
        return $this->translatedField($value->specificationValueTranslations, $lang, 'name', __('manager.specification_value.unnamed'));
    }

    public function getSpecificationName($specification, $lang)
    {
        if (! $specification) {
            return null;
        }

        return $this->translatedField($specification->specificationTranslations, $lang, 'name', (string) $specification->id);
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();
        $specifications = \App\Models\Specification::with('specificationTranslations')->get();

        return view('livewire.manager.specification-values', [
            'specificationValues' => $this->specificationValues,
            'lang' => $lang,
            'specifications' => $specifications,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
