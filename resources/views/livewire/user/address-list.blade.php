@php
    $locale = session('lang');
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
@endphp

<div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row gap-12 py-10">
   <div class="w-full md:w-1/4"> <x-profile-nav /></div>
    <div class="w-full md:w-3/4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">{{ __('addresses.my_addresses') }}</h2>
            <a href="{{ locaRoute('user.addresses.form') }}"
                class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                {{ __('addresses.add_new') }}
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($addresses as $address)
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <p class="font-bold">{{ $address->firstname }} {{ $address->lastname }}</p>
                            <p class="text-gray-600">{{ $address->email }}</p>
                            <p class="text-gray-600">{{ $address->telephone }}</p>
                        </div>
                        <div class="flex space-x-4">
                            <a href="{{ locaRoute('user.addresses.form', ['id' => $address->id]) }}"
                                class="text-teal-600 hover:text-teal-800">
                                <x-heroicon-o-pencil-square class="w-6 h-6" />
                            </a>
                            <button wire:click="deleteAddress({{ $address->id }})"
                                class="text-red-600 hover:text-red-800">
                                <x-heroicon-o-trash class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    <div class="text-gray-600">
                        <p>{{ $address->address_1 }}</p>
                        @if ($address->address_2)
                            <p>{{ $address->address_2 }}</p>
                        @endif
                        <p>{{ $address->city }}, {{ $address->zone?->zoneTranslations->where('language_id', $lang->id)->first()?->name ?? $address->zone?->name ?? '' }}</p>
                        <p>{{ $address->country->countryTranslations->where('language_id', $lang->id)->first()?->name ?? $address->country->name ?? '' }} {{ $address->postcode }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Delete Confirmation Modal -->
        @if ($showDeleteModal)
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold mb-4">{{ __('addresses.confirm_delete') }}</h3>
                    <div class="flex justify-end space-x-4">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition-colors">
                            {{ __('addresses.cancel') }}
                        </button>
                        <button wire:click="confirmDelete"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            {{ __('addresses.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('addresses.my_addresses') }}" />
@endPushOnce
