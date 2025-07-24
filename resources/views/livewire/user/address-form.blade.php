<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">
        {{ $address ? __('addresses.edit_address') : __('addresses.add_new') }}
    </h1>

    <form wire:submit="save" class="max-w-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-2">{{ __('addresses.firstname') }}</label>
                <input type="text" wire:model="state.firstname" class="w-full border rounded px-3 py-2">
                @error('state.firstname')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2">{{ __('addresses.lastname') }}</label>
                <input type="text" wire:model="state.lastname" class="w-full border rounded px-3 py-2">
                @error('state.lastname')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2">{{ __('addresses.telephone') }}</label>
                <input type="text" wire:model="state.telephone" class="w-full border rounded px-3 py-2">
                @error('state.telephone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2">{{ __('addresses.address_1') }}</label>
                <input type="text" wire:model="state.address_1" class="w-full border rounded px-3 py-2">
                @error('state.address_1')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2">{{ __('addresses.address_2') }}</label>
                <input type="text" wire:model="state.address_2" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-2">{{ __('addresses.city') }}</label>
                <input type="text" wire:model="state.city" class="w-full border rounded px-3 py-2">
                @error('state.city')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2">{{ __('addresses.country') }}</label>
                <select wire:model.live="state.country_id" class="w-full border rounded px-3 py-2">
                    <option value="">{{ __('addresses.select_country') }}</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
                @error('state.country_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2">{{ __('addresses.zone') }}</label>
                <select wire:model="state.zone_id" class="w-full border rounded px-3 py-2">
                    <option value="">{{ __('addresses.select_zone') }}</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                    @endforeach
                </select>
                @error('state.zone_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2">{{ __('addresses.postcode') }}</label>
                <input type="text" wire:model="state.postcode" class="w-full border rounded px-3 py-2">
                @error('state.postcode')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
            <a href="{{ route('user.addresses', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 border rounded">
                {{ __('addresses.cancel') }}
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ __('addresses.save') }}
            </button>
        </div>
    </form>
</div>
