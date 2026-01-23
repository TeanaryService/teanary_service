<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Address;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Addresses extends Component
{
    use HasBatchActions;
    use HasNavigationRedirect;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $filterCountryId = null;

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
        $this->flashMessage('deleted_successfully');
    }

    protected function getCurrentPageItems()
    {
        return $this->addresses->getCollection();
    }

    public function batchDeleteAddresses(): void
    {
        // 批量删除时需要检查订单关联
        if (empty($this->selectedItems)) {
            session()->flash('error', __('manager.batch.no_items_selected'));
            return;
        }

        $count = 0;
        $skipped = 0;
        foreach ($this->selectedItems as $id) {
            try {
                $address = Address::find($id);
                if ($address) {
                    $hasShippingOrders = \App\Models\Order::where('shipping_address_id', $address->id)->exists();
                    $hasBillingOrders = \App\Models\Order::where('billing_address_id', $address->id)->exists();
                    
                    if ($hasShippingOrders || $hasBillingOrders) {
                        $skipped++;
                        continue;
                    }
                    $address->delete();
                    $count++;
                }
            } catch (\Exception $e) {
                // 忽略删除失败的项目
            }
        }

        $this->selectedItems = [];
        $this->selectAll = false;
        $this->resetPage();

        if ($skipped > 0) {
            session()->flash('message', __('manager.batch.deleted_with_skipped', ['count' => $count, 'skipped' => $skipped]));
        } else {
            session()->flash('message', __('manager.batch.deleted_successfully', ['count' => $count]));
        }
    }

    #[Computed]
    public function addresses()
    {
        $lang = $this->getCurrentLanguage();

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
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
                // 搜索地址字段
                $q->orWhere('address_1', 'like', '%'.$search.'%')
                    ->orWhere('address_2', 'like', '%'.$search.'%');
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
            $countryName = $this->translatedField($address->country->countryTranslations, $lang, 'name', $address->country->name ?? '');
        }

        // 地区多语言
        $zoneName = '';
        if ($address->zone) {
            $zoneName = $this->translatedField($address->zone->zoneTranslations, $lang, 'name', $address->zone->name ?? '');
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
        if (! $country) {
            return '-';
        }

        return $this->translatedField($country->countryTranslations, $lang, 'name', $country->name ?? '-');
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();
        $countries = \App\Models\Country::with('countryTranslations')->get();

        return view('livewire.manager.addresses', [
            'addresses' => $this->addresses,
            'lang' => $lang,
            'countries' => $countries,
        ])->layout('components.layouts.manager');
    }
}
