<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Zone;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Zones extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $filterCountryId = null;
    public string $filterActive = '';
    public ?string $filterTranslationStatus = null;

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
        $this->filterTranslationStatus = null;
        $this->resetPage();
    }

    public function deleteZone(int $id): void
    {
        $this->deleteModel(Zone::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->zones->getCollection();
    }

    public function batchDeleteZones(): void
    {
        $this->batchDelete(Zone::class);
    }

    public function batchSetZoneTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(Zone::class, $status);
    }

    public function batchSetZoneActiveStatus(bool $active): void
    {
        $this->batchUpdateActiveStatus(Zone::class, $active);
    }

    #[Computed]
    public function zones()
    {
        $lang = $this->getCurrentLanguage();

        $query = Zone::query()
            ->with(['zoneTranslations', 'country.countryTranslations']);

        // 搜索：通过翻译名称搜索
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('zoneTranslations', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
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
        if ($this->filterTranslationStatus) {
            $query->where('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getZoneName($zone, $lang)
    {
        return $this->translatedField($zone->zoneTranslations, $lang, 'name', __('manager.zone.unnamed'));
    }

    public function getCountryName($country, $lang)
    {
        if (! $country) {
            return null;
        }

        return $this->translatedField($country->countryTranslations, $lang, 'name', $country->iso_code_2 ?? '');
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();
        $countries = \App\Models\Country::with('countryTranslations')->get();

        return view('livewire.manager.zones', [
            'zones' => $this->zones,
            'lang' => $lang,
            'countries' => $countries,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
