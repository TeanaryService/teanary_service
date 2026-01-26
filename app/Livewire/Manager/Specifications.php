<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Specification;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Specifications extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?string $filterTranslationStatus = null;

    public function updatingFilterTranslationStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterTranslationStatus = null;
        $this->resetPage();
    }

    public function deleteSpecification(int $id): void
    {
        $this->deleteModel(Specification::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->specifications->getCollection();
    }

    public function batchDeleteSpecifications(): void
    {
        $this->batchDelete(Specification::class);
    }

    public function batchSetSpecificationTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(Specification::class, $status);
    }

    #[Computed]
    public function specifications()
    {
        $lang = $this->getCurrentLanguage();

        $query = Specification::query()
            ->with(['specificationTranslations', 'specificationValues']);

        if ($this->search) {
            $query->whereHas('specificationTranslations', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterTranslationStatus) {
            $query->where('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getSpecificationName($specification, $lang)
    {
        return $this->translatedField($specification->specificationTranslations, $lang, 'name', __('manager.specification.unnamed'));
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();

        return view('livewire.manager.specifications', [
            'specifications' => $this->specifications,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
