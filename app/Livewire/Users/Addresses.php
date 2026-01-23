<?php

namespace App\Livewire\Users;

use App\Models\Address;
use App\Models\Country;
use App\Models\Zone;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Livewire\Traits\RequiresAuthentication;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Addresses extends Component
{
    use WithPagination;
    use UsesLocaleCurrency;
    use RequiresAuthentication;

    public $showForm = false;
    public $addressId = null;

    // 表单字段
    public $email = '';
    public $firstname = '';
    public $lastname = '';
    public $telephone = '';
    public $company = '';
    public $address_1 = '';
    public $address_2 = '';
    public $city = '';
    public $postcode = '';
    public $country_id = '';
    public $zone_id = '';

    public $zones = [];

    protected $rules = [
        'email' => 'required|email|max:255',
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'telephone' => 'required|string|max:255',
        'company' => 'nullable|string|max:255',
        'address_1' => 'required|string|max:255',
        'address_2' => 'nullable|string|max:255',
        'city' => 'required|string|max:255',
        'postcode' => 'required|string|max:20',
        'country_id' => 'required|exists:countries,id',
        'zone_id' => 'required|exists:zones,id',
    ];

    protected $messages = [
        'email.required' => '请输入邮箱地址',
        'email.email' => '请输入有效的邮箱地址',
        'firstname.required' => '请输入名字',
        'lastname.required' => '请输入姓氏',
        'telephone.required' => '请输入电话',
        'address_1.required' => '请输入详细地址',
        'city.required' => '请输入城市',
        'postcode.required' => '请输入邮编',
        'country_id.required' => '请选择国家',
        'zone_id.required' => '请选择省份/地区',
    ];

    public function mount(): void
    {
        $this->ensureAuthenticated();
    }

    public function updatedCountryId($value)
    {
        $this->zone_id = '';
        if ($value) {
            $lang = $this->getCurrentLanguage();
            $this->zones = Zone::getZonesByCountryAndLanguage($value, $lang?->id);
        } else {
            $this->zones = [];
        }
    }

    #[Computed]
    public function addresses()
    {
        return Address::query()
            ->where('user_id', Auth::id())
            ->where('deleted', false)
            ->with(['country.countryTranslations', 'zone.zoneTranslations'])
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function countries()
    {
        $lang = $this->getCurrentLanguage();

        return Country::getCountriesByLanguage($lang?->id);
    }

    public function createAddress(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editAddress(int $addressId): void
    {
        $address = Address::where('user_id', Auth::id())
            ->where('id', $addressId)
            ->where('deleted', false)
            ->firstOrFail();

        $this->addressId = $address->id;
        $this->email = $address->email;
        $this->firstname = $address->firstname;
        $this->lastname = $address->lastname;
        $this->telephone = $address->telephone;
        $this->company = $address->company ?? '';
        $this->address_1 = $address->address_1;
        $this->address_2 = $address->address_2 ?? '';
        $this->city = $address->city;
        $this->postcode = $address->postcode;
        $this->country_id = $address->country_id;
        $this->zone_id = $address->zone_id;

        // 加载 zones，但不重置 zone_id（因为我们已经设置了它）
        $lang = $this->getCurrentLanguage();
        $this->zones = Zone::getZonesByCountryAndLanguage($this->country_id, $lang?->id);

        $this->showForm = true;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function saveAddress(): void
    {
        $this->validate();

        // 保存 addressId，因为 resetForm 会清除它
        $addressId = $this->addressId;

        $data = [
            'user_id' => Auth::id(),
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'telephone' => $this->telephone,
            'company' => $this->company,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'country_id' => (int) $this->country_id,
            'zone_id' => (int) $this->zone_id,
        ];

        if ($addressId) {
            $address = Address::where('id', $addressId)
                ->where('user_id', Auth::id())
                ->where('deleted', false)
                ->firstOrFail();
            $address->update($data);
        } else {
            Address::create($data);
        }

        session()->flash('message', __('app.addresses.address_saved'));
        $this->resetForm();
        $this->resetPage();
    }

    public function deleteAddress(int $addressId): void
    {
        $address = Address::where('user_id', Auth::id())
            ->where('id', $addressId)
            ->where('deleted', false)
            ->firstOrFail();

        $address->update(['deleted' => true]);

        session()->flash('message', __('app.addresses.address_deleted'));
        $this->resetPage();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'addressId', 'email', 'firstname', 'lastname', 'telephone', 'company',
            'address_1', 'address_2', 'city', 'postcode', 'country_id', 'zone_id',
            'showForm', 'zones',
        ]);
    }

    public function render()
    {
        return view('livewire.users.addresses', [
            'addresses' => $this->addresses,
            'countries' => $this->countries,
        ])->layout('components.layouts.app');
    }
}
