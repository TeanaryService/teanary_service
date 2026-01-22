<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Country;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Countries extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterActive = '';
    public array $filterTranslationStatus = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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
        $country = Country::findOrFail($id);
        $country->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    #[Computed]
    public function countries()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = Country::query()
            ->with(['countryTranslations', 'zones']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('countryTranslations', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // 筛选：激活状态
        if ($this->filterActive !== '') {
            $query->where('active', $this->filterActive === '1');
        }

        // 筛选：翻译状态
        if (!empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getCountryName($country, $lang)
    {
        $translation = $country->countryTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $country->countryTranslations->first();
        return $first ? $first->name : __('manager.country.unnamed');
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return view('livewire.manager.countries', [
            'countries' => $this->countries,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
