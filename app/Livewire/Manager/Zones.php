<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Zone;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

class Zones extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filterCountryId = null;
    public string $filterActive = '';
    public array $filterTranslationStatus = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCountryId(): void
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
        $this->filterCountryId = null;
        $this->filterActive = '';
        $this->filterTranslationStatus = [];
        $this->resetPage();
    }

    public function deleteZone(int $id): void
    {
        $zone = Zone::findOrFail($id);
        $zone->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    public function getZonesProperty()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = Zone::query()
            ->with(['zoneTranslations', 'country.countryTranslations']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('zoneTranslations', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // 筛选：国家
        if ($this->filterCountryId) {
            $query->where('country_id', $this->filterCountryId);
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

    public function getZoneName($zone, $lang)
    {
        $translation = $zone->zoneTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $zone->zoneTranslations->first();
        return $first ? $first->name : __('manager.zone.unnamed');
    }

    public function getCountryName($country, $lang)
    {
        if (!$country) {
            return null;
        }
        $translation = $country->countryTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $country->countryTranslations->first();
        return $first ? $first->name : $country->iso_code_2;
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $countries = \App\Models\Country::with('countryTranslations')->get();

        return view('livewire.manager.zones', [
            'zones' => $this->zones,
            'lang' => $lang,
            'countries' => $countries,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
