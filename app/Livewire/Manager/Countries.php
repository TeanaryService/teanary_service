<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Country;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Countries extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public string $filterActive = '';
    public array $filterTranslationStatus = [];

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
        $this->filterActive = '';
        $this->filterTranslationStatus = [];
        $this->resetPage();
    }

    public function deleteCountry(int $id): void
    {
        $this->deleteModel(Country::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->countries->getCollection();
    }

    public function batchDeleteCountries(): void
    {
        $this->batchDelete(Country::class);
    }

    public function batchSetCountryTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(Country::class, $status);
    }

    public function batchSetCountryActiveStatus(bool $active): void
    {
        $this->batchUpdateActiveStatus(Country::class, $active);
    }

    #[Computed]
    public function countries()
    {
        $lang = $this->getCurrentLanguage();

        $query = Country::query()
            ->with(['countryTranslations', 'zones']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('countryTranslations', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        // 筛选：激活状态
        if ($this->filterActive !== '') {
            $query->where('active', $this->filterActive === '1');
        }

        // 筛选：翻译状态
        if (! empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getCountryName($country, $lang)
    {
        return $this->translatedField($country->countryTranslations, $lang, 'name', __('manager.country.unnamed'));
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();

        return view('livewire.manager.countries', [
            'countries' => $this->countries,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
