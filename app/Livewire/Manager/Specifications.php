<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Specification;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

class Specifications extends Component
{
    use WithPagination;

    public string $search = '';
    public array $filterTranslationStatus = [];

    public function updatingSearch(): void
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
        $this->filterTranslationStatus = [];
        $this->resetPage();
    }

    public function deleteSpecification(int $id): void
    {
        $spec = Specification::findOrFail($id);
        $spec->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    public function getSpecificationsProperty()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = Specification::query()
            ->with(['specificationTranslations', 'specificationValues']);

        if ($this->search) {
            $query->whereHas('specificationTranslations', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getSpecificationName($specification, $lang)
    {
        $translation = $specification->specificationTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $specification->specificationTranslations->first();
        return $first ? $first->name : __('manager.specification.unnamed');
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return view('livewire.manager.specifications', [
            'specifications' => $this->specifications,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

