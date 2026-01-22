<?php

namespace App\Livewire\Manager;

use App\Models\Address;
use App\Services\LocaleCurrencyService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Addresses extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filterCountryId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCountryId(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterCountryId = null;
        $this->resetPage();
    }

    public function deleteAddress(int $id): void
    {
        $address = Address::findOrFail($id);
        
        // 检查是否有关联订单（作为收货地址或账单地址）
        $hasShippingOrders = \App\Models\Order::where('shipping_address_id', $address->id)->exists();
        $hasBillingOrders = \App\Models\Order::where('billing_address_id', $address->id)->exists();
        
        if ($hasShippingOrders || $hasBillingOrders) {
            session()->flash('error', __('manager.addresses.cannot_delete_has_orders'));
            return;
        }
        
        $address->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    #[Computed]
    public function addresses()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = Address::query()
            ->with(['user', 'country.countryTranslations', 'zone.zoneTranslations', 'orders']);

        // 搜索：用户名、用户ID、address1、address2
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                // 搜索用户ID（如果是数字）
                if (is_numeric($search)) {
                    $q->where('user_id', $search);
                }
                // 搜索用户名或邮箱
                $q->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                              ->orWhere('email', 'like', '%' . $search . '%');
                });
                // 搜索地址字段
                $q->orWhere('address_1', 'like', '%' . $search . '%')
                  ->orWhere('address_2', 'like', '%' . $search . '%');
            });
        }

        // 筛选：国家
        if ($this->filterCountryId) {
            $query->where('country_id', $this->filterCountryId);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getFullAddress($address, $lang)
    {
        // 国家多语言
        $countryName = '';
        if ($address->country) {
            $translation = $address->country->countryTranslations->where('language_id', $lang?->id)->first();
            $countryName = $translation && $translation->name
                ? $translation->name
                : ($address->country->countryTranslations->first()->name ?? $address->country->name ?? '');
        }

        // 地区多语言
        $zoneName = '';
        if ($address->zone) {
            $translation = $address->zone->zoneTranslations->where('language_id', $lang?->id)->first();
            $zoneName = $translation && $translation->name
                ? $translation->name
                : ($address->zone->zoneTranslations->first()->name ?? $address->zone->name ?? '');
        }

        // 拼接完整地址
        $parts = [];
        $addressLine = trim("{$address->address_1} {$address->address_2}");
        if ($addressLine) {
            $parts[] = $addressLine;
        }
        $cityLine = trim(implode(' ', array_filter([
            $address->city,
            $zoneName,
            $countryName,
            $address->postcode,
        ])));
        if ($cityLine) {
            $parts[] = $cityLine;
        }

        return count($parts) ? implode(', ', $parts) : '-';
    }

    public function getCountryName($country, $lang)
    {
        if (!$country) {
            return '-';
        }
        $translation = $country->countryTranslations->where('language_id', $lang?->id)->first();
        return $translation && $translation->name
            ? $translation->name
            : ($country->countryTranslations->first()->name ?? $country->name ?? '-');
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $countries = \App\Models\Country::with('countryTranslations')->get();

        return view('livewire.manager.addresses', [
            'addresses' => $this->addresses,
            'lang' => $lang,
            'countries' => $countries,
        ])->layout('components.layouts.manager');
    }
}
