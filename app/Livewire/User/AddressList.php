<?php

namespace App\Livewire\User;

use App\Models\Address;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AddressList extends Component
{
    public bool $showDeleteModal = false;
    public ?Address $addressToDelete = null;

    public function deleteAddress(Address $address): void
    {
        $this->addressToDelete = $address;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if ($this->addressToDelete) {
            // $this->addressToDelete->delete();
            $this->addressToDelete->deleted = true;
            $this->addressToDelete->save();

            $this->addressToDelete = null;
            $this->showDeleteModal = false;
            session()->flash('message', __('addresses.address_deleted'));
        }
    }

    public function render(): View
    {
        $addresses = Address::where('user_id', auth()->id())
            ->where('deleted', false)
            ->with(['country.countryTranslations', 'zone.zoneTranslations'])
            ->get();

        return view('livewire.user.address-list', compact('addresses'));
    }
}
