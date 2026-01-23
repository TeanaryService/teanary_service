@php
    $breadcrumbs = buildUserCenterBreadcrumbs('addresses', __('app.addresses.title'));
    $localeService = app(\App\Services\LocaleCurrencyService::class);
    $lang = $localeService->getLanguageByCode(session('lang'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[80vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-users.sidebar active="addresses" />
            
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('app.addresses.my_addresses') }}</h1>
            @if(!$showForm)
                <x-widgets.button wire:click="createAddress" class="inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('app.addresses.add_new') }}
                </x-widgets.button>
            @endif
        </div>

        <x-widgets.session-message type="message" />

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
                        <form wire:submit="saveAddress">
                            <x-widgets.form-container class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <x-widgets.form-field :label="__('app.email')" labelFor="email" error="email">
                                    <x-widgets.input type="email" id="email" wire="email" error="email" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.company')" labelFor="company" error="company">
                                    <x-widgets.input type="text" id="company" wire="company" error="company" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.addresses.firstname')" labelFor="firstname" error="firstname">
                                    <x-widgets.input type="text" id="firstname" wire="firstname" error="firstname" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.addresses.lastname')" labelFor="lastname" error="lastname">
                                    <x-widgets.input type="text" id="lastname" wire="lastname" error="lastname" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.addresses.telephone')" labelFor="telephone" error="telephone">
                                    <x-widgets.input type="text" id="telephone" wire="telephone" error="telephone" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.addresses.postcode')" labelFor="postcode" error="postcode">
                                    <x-widgets.input type="text" id="postcode" wire="postcode" error="postcode" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.addresses.address_1')" labelFor="address_1" error="address_1" class="md:col-span-2">
                                    <x-widgets.input type="text" id="address_1" wire="address_1" error="address_1" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.addresses.address_2')" labelFor="address_2" error="address_2" class="md:col-span-2">
                                    <x-widgets.input type="text" id="address_2" wire="address_2" error="address_2" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.addresses.city')" labelFor="city" error="city">
                                    <x-widgets.input type="text" id="city" wire="city" error="city" class="sm:text-sm rounded-md" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.addresses.country')" labelFor="country_id" error="country_id">
                                    <x-widgets.select 
                                        id="country_id" 
                                        wire="live=country_id"
                                        :options="[['value' => '', 'label' => __('app.select_country')], ...collect($countries)->map(fn($c) => ['value' => $c['id'], 'label' => $c['name']])->toArray()]"
                                        error="country_id"
                                        class="sm:text-sm rounded-md"
                                    />
                                </x-widgets.form-field>
                                @if($country_id)
                                    <x-widgets.form-field :label="__('app.addresses.zone')" labelFor="zone_id" error="zone_id">
                                        <x-widgets.select 
                                            id="zone_id" 
                                            wire="zone_id"
                                            :options="[['value' => '', 'label' => __('app.select_zone')], ...collect($zones)->map(fn($z) => ['value' => $z['id'], 'label' => $z['name']])->toArray()]"
                                            error="zone_id"
                                            class="sm:text-sm rounded-md"
                                        />
                                    </x-widgets.form-field>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-3 pt-4">
                                <x-widgets.button 
                                    type="button" 
                                    wire:click="cancelEdit"
                                    variant="secondary"
                                >
                                    {{ __('app.cancel') }}
                                </x-widgets.button>
                                <x-widgets.button type="submit">
                                    {{ __('app.addresses.save') }}
                                </x-widgets.button>
                            </div>
                            </x-widgets.form-container>
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
                                    <x-widgets.button 
                                        wire:click="editAddress({{ $address->id }})" 
                                        variant="secondary"
                                        class="flex-1 inline-flex items-center justify-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        {{ __('app.addresses.edit_address') }}
                                    </x-widgets.button>
                                    <x-widgets.button 
                                        wire:click="deleteAddress({{ $address->id }})" 
                                        wire:confirm="{{ __('app.addresses.confirm_delete') }}"
                                        variant="danger-outline"
                                        class="inline-flex items-center justify-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        {{ __('app.addresses.delete') }}
                                    </x-widgets.button>
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

<x-seo-meta title="{{ __('app.addresses.title') }}" />
