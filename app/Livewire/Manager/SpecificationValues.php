<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\SpecificationValue;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

class SpecificationValues extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filterSpecificationId = null;
    public array $filterTranslationStatus = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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
        $value = SpecificationValue::findOrFail($id);
        $value->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    public function getSpecificationValuesProperty()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = SpecificationValue::query()
            ->with(['specification.specificationTranslations', 'specificationValueTranslations']);

        if ($this->search) {
            $query->whereHas('specificationValueTranslations', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
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
        $translation = $value->specificationValueTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $value->specificationValueTranslations->first();
        return $first ? $first->name : __('manager.specification_value.unnamed');
    }

    public function getSpecificationName($specification, $lang)
    {
        if (! $specification) {
            return null;
        }
        $translation = $specification->specificationTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $specification->specificationTranslations->first();
        return $first ? $first->name : $specification->id;
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $specifications = \App\Models\Specification::with('specificationTranslations')->get();

        return view('livewire.manager.specification-values', [
            'specificationValues' => $this->specificationValues,
            'lang' => $lang,
            'specifications' => $specifications,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

