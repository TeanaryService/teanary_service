@php
    $isEdit = $addressId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('addresses', $isEdit ? __('app.edit') : __('app.create'), __('manager.addresses.label'), locaRoute('manager.addresses'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="addresses" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.addresses.label') }}
                    </h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                <form wire:submit="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        {{-- 用户信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.addresses.user_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 用户 --}}
                                <div>
                                    <label for="userId" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.user_id') }}
                                    </label>
                                    <select 
                                        id="userId"
                                        wire:model="userId"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('userId') border-red-300 @enderror"
                                    >
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('userId')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.addresses.user_id_helper') }}</p>
                                </div>

                                {{-- 名字 --}}
                                <div>
                                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.firstname') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="firstname"
                                        wire:model="firstname"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('firstname') border-red-300 @enderror"
                                    />
                                    @error('firstname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 姓氏 --}}
                                <div>
                                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.lastname') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="lastname"
                                        wire:model="lastname"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('lastname') border-red-300 @enderror"
                                    />
                                    @error('lastname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 邮箱 --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.email') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="email" 
                                        id="email"
                                        wire:model="email"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('email') border-red-300 @enderror"
                                    />
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 电话 --}}
                                <div>
                                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.telephone') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="tel" 
                                        id="telephone"
                                        wire:model="telephone"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('telephone') border-red-300 @enderror"
                                    />
                                    @error('telephone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 公司 --}}
                                <div>
                                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.company') }}
                                    </label>
                                    <input 
                                        type="text" 
                                        id="company"
                                        wire:model="company"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('company') border-red-300 @enderror"
                                    />
                                    @error('company')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 地址信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.addresses.address_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 国家 --}}
                                <div>
                                    <label for="countryId" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.country_id') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        id="countryId"
                                        wire:model.live="countryId"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('countryId') border-red-300 @enderror"
                                    >
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}">
                                                @php
                                                    $translation = $country->countryTranslations->where('language_id', $lang?->id)->first();
                                                    $countryName = $translation ? $translation->name : ($country->countryTranslations->first() ? $country->countryTranslations->first()->name : $country->iso_code_2);
                                                @endphp
                                                {{ $countryName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('countryId')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 地区 --}}
                                <div>
                                    <label for="zoneId" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.zone_id') }}
                                    </label>
                                    <select 
                                        id="zoneId"
                                        wire:model="zoneId"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('zoneId') border-red-300 @enderror"
                                        @if(empty($zones)) disabled @endif
                                    >
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($zones as $zoneId => $zoneName)
                                            <option value="{{ $zoneId }}">{{ $zoneName }}</option>
                                        @endforeach
                                    </select>
                                    @error('zoneId')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 详细地址1 --}}
                                <div class="md:col-span-2">
                                    <label for="address1" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.address_1') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="address1"
                                        wire:model="address1"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('address1') border-red-300 @enderror"
                                    />
                                    @error('address1')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 详细地址2 --}}
                                <div class="md:col-span-2">
                                    <label for="address2" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.address_2') }}
                                    </label>
                                    <input 
                                        type="text" 
                                        id="address2"
                                        wire:model="address2"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('address2') border-red-300 @enderror"
                                    />
                                    @error('address2')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 城市 --}}
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.city') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="city"
                                        wire:model="city"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('city') border-red-300 @enderror"
                                    />
                                    @error('city')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 邮编 --}}
                                <div>
                                    <label for="postcode" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.addresses.postcode') }}
                                    </label>
                                    <input 
                                        type="text" 
                                        id="postcode"
                                        wire:model="postcode"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('postcode') border-red-300 @enderror"
                                    />
                                    @error('postcode')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <a href="{{ locaRoute('manager.addresses') }}" 
                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                {{ __('app.cancel') }}
                            </a>
                            <button 
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors"
                            >
                                {{ __('app.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.addresses.label') }}" keywords="{{ __('manager.addresses.label') }}" />
