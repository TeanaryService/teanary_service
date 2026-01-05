<div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row gap-12 py-10">
    <div class="w-full md:w-1/4"> <x-profile-nav /></div>
    <div class="w-full md:w-3/4">
        <h2 class="text-2xl font-bold mb-6">
            {{ $address ? __('app.addresses.edit_address') : __('app.addresses.add_new') }}
        </h2>

        <form wire:submit="save" class="space-y-6 bg-white p-6 rounded-xl shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.email') }}</label>
                    <input type="text" wire:model="state.email"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                    @error('state.email')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.firstname') }}</label>
                    <input type="text" wire:model="state.firstname"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                    @error('state.firstname')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.lastname') }}</label>
                    <input type="text" wire:model="state.lastname"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                    @error('state.lastname')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.telephone') }}</label>
                    <input type="text" wire:model="state.telephone"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                    @error('state.telephone')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.address_1') }}</label>
                    <input type="text" wire:model="state.address_1"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                    @error('state.address_1')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.address_2') }}</label>
                    <input type="text" wire:model="state.address_2"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.city') }}</label>
                    <input type="text" wire:model="state.city"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                    @error('state.city')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.country') }}</label>
                    <select wire:model.live="state.country_id"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                        <option value="">{{ __('app.addresses.select_country') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                        @endforeach
                    </select>
                    @error('state.country_id')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.zone') }}</label>
                    <select wire:model="state.zone_id"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                        <option value="">{{ __('app.addresses.select_zone') }}</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                        @endforeach
                    </select>
                    @error('state.zone_id')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">{{ __('app.addresses.postcode') }}</label>
                    <input type="text" wire:model="state.postcode"
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200">
                    @error('state.postcode')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('user.addresses', ['locale' => app()->getLocale()]) }}"
                    class="px-6 py-2 border rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('app.addresses.cancel') }}
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                    {{ __('app.addresses.save') }}
                </button>
            </div>
        </form>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ $address ? __('app.addresses.edit_address') : __('app.addresses.add_new') }}" />
@endPushOnce
