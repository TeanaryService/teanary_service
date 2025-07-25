<?php

namespace App\Livewire\User;

use App\Models\Address;
use App\Models\Country;
use App\Models\Zone;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AddressForm extends Component
{
    public ?Address $address = null;
    public array $state = [];
    public array $zones = [];

    public function mount(): void
    {
        if ($addressId = request()->query('id')) {
            $this->address = Address::findOrFail($addressId);
            $this->state = $this->address->toArray();
            if ($this->address->country_id) {
                $this->loadZones($this->address->country_id);
            }
        }
    }

    public function loadZones($countryId): void
    {
        $this->zones = Zone::getZonesByCountryAndLanguage($countryId);
    }

    public function updatedStateCountryId($value): void
    {
        $this->loadZones($value);
        $this->state['zone_id'] = '';
    }

    public function save()
    {
        $zones = Zone::getZonesByCountryAndLanguage($this->state['country_id'] ?? null);

        $rules = [
            'state.email' => 'required|email',
            'state.firstname' => 'required|string|max:255',
            'state.lastname' => 'required|string|max:255',
            'state.telephone' => 'required|string|max:255',
            'state.address_1' => 'required|string|max:255',
            'state.city' => 'required|string|max:255',
            'state.country_id' => 'required|exists:countries,id',
            'state.postcode' => 'required|string|max:20',
        ];

        if (!empty($zones)) {
            $rules['state.zone_id'] = 'required|exists:zones,id';
        } else {
            $rules['state.zone_id'] = 'nullable|exists:zones,id';
        }

        $this->validate($rules);

        $this->state['user_id'] = auth()->id();

        if ($this->address) {
            $this->address->update($this->state);
        } else {
            Address::create($this->state);
        }

        session()->flash('message', __('addresses.address_saved'));

        return redirect()->route('user.addresses', ['locale' => app()->getLocale()]);
    }

    public function render(): View
    {
        return view('livewire.user.address-form', [
            'countries' => Country::getCountriesByLanguage(),
        ]);
    }
}
