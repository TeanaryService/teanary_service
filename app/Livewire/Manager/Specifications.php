<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Specification;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Specifications extends Component
{
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public array $filterTranslationStatus = [];

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
        $this->deleteModel(Specification::class, $id);
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

        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
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
