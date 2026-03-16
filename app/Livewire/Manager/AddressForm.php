<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use App\Models\Zone;
use Livewire\Component;

class AddressForm extends Component
{
    use HasNavigationRedirect;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $addressId = null;
    public ?int $userId = null;
    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public string $telephone = '';
    public ?string $company = null;
    public ?int $countryId = null;
    public ?int $zoneId = null;
    public string $address1 = '';
    public ?string $address2 = null;
    public string $city = '';
    public ?string $postcode = null;
    public array $zones = [];

    protected array $rules = [
        'userId' => 'nullable|exists:users,id',
        'firstname' => 'required|max:255',
        'lastname' => 'required|max:255',
        'email' => 'required|email|max:255',
        'telephone' => 'required|max:255',
        'company' => 'nullable|max:255',
        'countryId' => 'required|exists:countries,id',
        'zoneId' => 'nullable|exists:zones,id',
        'address1' => 'required|max:255',
        'address2' => 'nullable|max:255',
        'city' => 'required|max:255',
        'postcode' => 'nullable|max:255',
    ];

    protected array $messages = [
        'userId.exists' => '选择的用户不存在',
        'firstname.required' => '名字不能为空',
        'lastname.required' => '姓氏不能为空',
        'email.required' => '邮箱不能为空',
        'email.email' => '请输入有效的邮箱地址',
        'telephone.required' => '电话不能为空',
        'countryId.required' => '国家不能为空',
        'countryId.exists' => '选择的国家不存在',
        'zoneId.exists' => '选择的地区不存在',
        'address1.required' => '详细地址1不能为空',
        'city.required' => '城市不能为空',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->addressId = $id;
            $address = Address::findOrFail($id);
            $this->userId = $address->user_id;
            $this->firstname = $address->firstname ?? '';
            $this->lastname = $address->lastname ?? '';
            $this->email = $address->email ?? '';
            $this->telephone = $address->telephone ?? '';
            $this->company = $address->company;
            $this->countryId = $address->country_id;
            $this->zoneId = $address->zone_id;
            $this->address1 = $address->address_1 ?? '';
            $this->address2 = $address->address_2;
            $this->city = $address->city ?? '';
            $this->postcode = $address->postcode;

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
        if ($this->countryId) {
            $lang = $this->getCurrentLanguage();

            $zones = Zone::where('country_id', $this->countryId)
                ->with('zoneTranslations')
                ->get();

            $this->zones = [];
            foreach ($zones as $zone) {
                $this->zones[$zone->id] = $this->translatedField(
                    $zone->zoneTranslations,
                    $lang,
                    'name',
                    (string) ($zone->name ?? '')
                );
            }
        } else {
            $this->zones = [];
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => $this->userId,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'company' => $this->company,
            'country_id' => $this->countryId,
            'zone_id' => $this->zoneId,
            'address_1' => $this->address1,
            'address_2' => $this->address2,
            'city' => $this->city,
            'postcode' => $this->postcode,
        ];

        if ($this->addressId) {
            $address = Address::findOrFail($this->addressId);
            $address->update($data);
            $this->flashMessage('updated_successfully');
        } else {
            Address::create($data);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.addresses', $this->addressId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        $users = User::orderBy('name')->get();
        $countries = Country::with('countryTranslations')->get();

        return view('livewire.manager.address-form', [
            'users' => $users,
            'countries' => $countries,
            'lang' => $this->getCurrentLanguage(),
        ])->layout('components.layouts.manager');
    }
}
