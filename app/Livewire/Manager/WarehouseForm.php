<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Country;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Services\WarehouseService;
use Livewire\Component;

class WarehouseForm extends Component
{
    use HasNavigationRedirect;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $warehouseId = null;
    public string $name = '';
    public string $code = '';
    public ?string $telephone = null;
    public ?string $address1 = null;
    public ?string $address2 = null;
    public ?string $city = null;
    public ?string $postcode = null;
    public ?int $countryId = null;
    public ?int $zoneId = null;
    public bool $active = true;
    public bool $isDefault = false;
    public int $sortOrder = 0;
    public array $zones = [];

    protected array $rules = [
        'name' => 'required|max:255',
        'code' => 'required|max:32',
        'telephone' => 'nullable|max:50',
        'address1' => 'nullable|max:255',
        'address2' => 'nullable|max:255',
        'city' => 'nullable|max:255',
        'postcode' => 'nullable|max:20',
        'countryId' => 'nullable|exists:countries,id',
        'zoneId' => 'nullable|exists:zones,id',
        'active' => 'boolean',
        'isDefault' => 'boolean',
        'sortOrder' => 'integer|min:0',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->warehouseId = $id;
            $w = Warehouse::findOrFail($id);
            $this->name = $w->name;
            $this->code = $w->code;
            $this->telephone = $w->telephone;
            $this->address1 = $w->address_1;
            $this->address2 = $w->address_2;
            $this->city = $w->city;
            $this->postcode = $w->postcode;
            $this->countryId = $w->country_id;
            $this->zoneId = $w->zone_id;
            $this->active = $w->active;
            $this->isDefault = $w->is_default;
            $this->sortOrder = (int) $w->sort_order;
            $this->loadZones();
        }
    }

    public function updatedCountryId(): void
    {
        $this->zoneId = null;
        $this->loadZones();
    }

    public function loadZones(): void
    {
        if (! $this->countryId) {
            $this->zones = [];

            return;
        }
        $lang = $this->getCurrentLanguage();
        $zones = Zone::where('country_id', $this->countryId)
            ->with('zoneTranslations')
            ->get();
        $this->zones = [];
        foreach ($zones as $zone) {
            $this->zones[] = [
                'id' => $zone->id,
                'name' => $this->translatedField($zone->zoneTranslations, $lang, 'name', (string) $zone->id),
            ];
        }
    }

    public function updatedIsDefault($value): void
    {
        if ($value) {
            Warehouse::where('id', '!=', $this->warehouseId)->update(['is_default' => false]);
        }
    }

    public function save()
    {
        if ($this->warehouseId) {
            $this->rules['code'] = 'required|max:32|unique:warehouses,code,'.$this->warehouseId;
        } else {
            $this->rules['code'] = 'required|max:32|unique:warehouses,code';
        }
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'telephone' => $this->telephone,
            'address_1' => $this->address1,
            'address_2' => $this->address2,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'country_id' => $this->countryId,
            'zone_id' => $this->zoneId,
            'active' => $this->active,
            'is_default' => $this->isDefault,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->isDefault) {
            Warehouse::where('id', '!=', $this->warehouseId)->update(['is_default' => false]);
        }

        if ($this->warehouseId) {
            $w = Warehouse::findOrFail($this->warehouseId);
            $w->update($data);
            $this->flashMessage('updated_successfully');
        } else {
            Warehouse::create($data);
            $this->flashMessage('created_successfully');
        }

        app(WarehouseService::class)->clearWarehousesCache();

        return $this->redirectWithMessage('manager.warehouses', $this->warehouseId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        $countries = Country::getCountriesByLanguage($this->getCurrentLanguage()?->id);

        return view('livewire.manager.warehouse-form', [
            'countries' => $countries,
        ])->layout('components.layouts.manager');
    }
}
