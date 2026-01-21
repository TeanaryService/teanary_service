@php
    $breadcrumbs = buildUserCenterBreadcrumbs('addresses', __('app.addresses.title'));
    $localeService = app(\App\Services\LocaleCurrencyService::class);
    $lang = $localeService->getLanguageByCode(session('lang'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-users.sidebar active="addresses" />
            
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('app.addresses.my_addresses') }}</h1>
            @if(!$showForm)
                <button wire:click="createAddress" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('app.addresses.add_new') }}
                </button>
            @endif
        </div>

        @if (session()->has('message'))
            <div class="mb-4 rounded-md bg-teal-50 p-4">
                <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
            </div>
        @endif

        <div class="space-y-6">
            @if($showForm)
                <!-- 地址表单 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $addressId ? __('app.addresses.edit_address') : __('app.addresses.add_new') }}
                            </h3>
                            <button wire:click="cancelEdit" type="button" 
                                    class="text-gray-500 hover:text-gray-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <form wire:submit="saveAddress" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.email') }}</label>
                                    <input type="email" id="email" wire:model="email"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="company" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.company') }}</label>
                                    <input type="text" id="company" wire:model="company"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('company') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.firstname') }}</label>
                                    <input type="text" id="firstname" wire:model="firstname"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('firstname') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.lastname') }}</label>
                                    <input type="text" id="lastname" wire:model="lastname"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('lastname') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.telephone') }}</label>
                                    <input type="text" id="telephone" wire:model="telephone"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('telephone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="postcode" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.postcode') }}</label>
                                    <input type="text" id="postcode" wire:model="postcode"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('postcode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label for="address_1" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.address_1') }}</label>
                                    <input type="text" id="address_1" wire:model="address_1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('address_1') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label for="address_2" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.address_2') }}</label>
                                    <input type="text" id="address_2" wire:model="address_2"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('address_2') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.city') }}</label>
                                    <input type="text" id="city" wire:model="city"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.country') }}</label>
                                    <select id="country_id" wire:model.live="country_id"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                        <option value="">{{ __('app.select_country') }}</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                @if($country_id)
                                    <div>
                                        <label for="zone_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.addresses.zone') }}</label>
                                        <select id="zone_id" wire:model="zone_id"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                            <option value="">{{ __('app.select_zone') }}</option>
                                            @foreach($zones as $zone)
                                                <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('zone_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-3 pt-4">
                                <button type="button" wire:click="cancelEdit" 
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    {{ __('app.cancel') }}
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 text-sm font-medium text-white bg-teal-600 border border-transparent rounded-lg hover:bg-teal-700 transition-colors">
                                    {{ __('app.addresses.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($addresses->isEmpty() && !$showForm)
                <!-- 空状态 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-16 text-center">
                    <div class="max-w-md mx-auto">
                        <svg class="mx-auto h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900">{{ __('app.addresses.no_addresses') }}</h3>
                        <p class="mt-2 text-sm text-gray-500">{{ __('app.addresses.add_first_address') }}</p>
                        <div class="mt-6">
                            <button wire:click="createAddress" 
                                    class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('app.addresses.add_new') }}
                            </button>
                        </div>
                    </div>
                </div>
            @elseif(!$showForm)
                <!-- 地址列表 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($addresses as $address)
                        @php
                            $countryName = $address->country?->countryTranslations
                                ->where('language_id', $lang?->id)
                                ->first()?->name 
                                ?? $address->country?->countryTranslations->first()?->name 
                                ?? $address->country?->name 
                                ?? '';
                            $zoneName = $address->zone?->zoneTranslations
                                ->where('language_id', $lang?->id)
                                ->first()?->name 
                                ?? $address->zone?->zoneTranslations->first()?->name 
                                ?? $address->zone?->name 
                                ?? '';
                        @endphp

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            {{ $address->firstname }} {{ $address->lastname }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm text-gray-600">
                                    @if($address->company)
                                        <p class="font-medium text-gray-900">{{ $address->company }}</p>
                                    @endif
                                    <p>{{ $address->address_1 }}</p>
                                    @if($address->address_2)
                                        <p>{{ $address->address_2 }}</p>
                                    @endif
                                    <p>
                                        {{ $address->city }}, 
                                        @if($zoneName) {{ $zoneName }}, @endif
                                        {{ $address->postcode }}
                                    </p>
                                    <p>{{ $countryName }}</p>
                                    <div class="pt-2 border-t border-gray-100">
                                        <p class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            {{ $address->telephone }}
                                        </p>
                                        @if($address->email)
                                            <p class="flex items-center gap-2 mt-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                {{ $address->email }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center gap-3">
                                    <button wire:click="editAddress({{ $address->id }})" 
                                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        {{ __('app.addresses.edit_address') }}
                                    </button>
                                    <button wire:click="deleteAddress({{ $address->id }})" 
                                            wire:confirm="{{ __('app.addresses.confirm_delete') }}"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        {{ __('app.addresses.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- 分页 -->
                <div class="mt-6">
                    {{ $addresses->links() }}
                </div>
            @endif
        </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('app.addresses.title') }}" description="{{ __('app.addresses.title') }}"
        keywords="{{ __('app.addresses.title') }}" />
@endPushOnce
