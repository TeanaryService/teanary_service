<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ __('addresses.my_addresses') }}</h1>
        <a href="{{ locaRoute('user.addresses.form') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            {{ __('addresses.add_new') }}
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($addresses as $address)
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <p class="font-bold">{{ $address->firstname }} {{ $address->lastname }}</p>
                        <p class="text-gray-600">{{ $address->telephone }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ locaRoute('user.addresses.form', ['id' => $address->id]) }}"
                            class="text-blue-600 hover:text-blue-800">
                            <x-heroicon-o-arrow-left-on-rectangle class="w-6 h-6" />
                        </a>
                        <button wire:click="deleteAddress({{ $address->id }})" class="text-red-600 hover:text-red-800">
                            <x-heroicon-o-arrow-left-on-rectangle class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <div class="text-gray-600">
                    <p>{{ $address->address_1 }}</p>
                    @if ($address->address_2)
                        <p>{{ $address->address_2 }}</p>
                    @endif
                    <p>{{ $address->city }}, {{ $address->zone->name }}</p>
                    <p>{{ $address->country->name }} {{ $address->postcode }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg">
                <h3 class="text-lg font-bold mb-4">{{ __('addresses.confirm_delete') }}</h3>
                <div class="flex justify-end space-x-4">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 border rounded">
                        {{ __('addresses.cancel') }}
                    </button>
                    <button wire:click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded">
                        {{ __('addresses.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
