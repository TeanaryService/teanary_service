@php
    $isEdit = $addressId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('addresses', $isEdit ? __('app.edit') : __('app.create'), __('manager.addresses.label'), locaRoute('manager.addresses'));
@endphp

<div class="min-h-[70vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="addresses" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.addresses.label') }}
                    </h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-100 p-4">
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
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.user_id')"
                                    labelFor="userId"
                                    error="userId"
                                    :help="__('manager.addresses.user_id_helper')"
                                >
                                    <x-widgets.select 
                                        id="userId"
                                        wire="userId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($users)->map(fn($user) => ['value' => $user->id, 'label' => $user->name])->toArray()]"
                                        error="userId"
                                    />
                                </x-widgets.form-field>

                                {{-- 名字 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.firstname')"
                                    labelFor="firstname"
                                    required
                                    error="firstname"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="firstname"
                                        wire="firstname"
                                        error="firstname"
                                    />
                                </x-widgets.form-field>

                                {{-- 姓氏 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.lastname')"
                                    labelFor="lastname"
                                    required
                                    error="lastname"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="lastname"
                                        wire="lastname"
                                        error="lastname"
                                    />
                                </x-widgets.form-field>

                                {{-- 邮箱 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.email')"
                                    labelFor="email"
                                    required
                                    error="email"
                                >
                                    <x-widgets.input 
                                        type="email" 
                                        id="email"
                                        wire="email"
                                        error="email"
                                    />
                                </x-widgets.form-field>

                                {{-- 电话 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.telephone')"
                                    labelFor="telephone"
                                    required
                                    error="telephone"
                                >
                                    <x-widgets.input 
                                        type="tel" 
                                        id="telephone"
                                        wire="telephone"
                                        error="telephone"
                                    />
                                </x-widgets.form-field>

                                {{-- 公司 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.company')"
                                    labelFor="company"
                                    error="company"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="company"
                                        wire="company"
                                        error="company"
                                    />
                                </x-widgets.form-field>
                            </div>
                        </div>

                        {{-- 地址信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.addresses.address_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 国家 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.country_id')"
                                    labelFor="countryId"
                                    required
                                    error="countryId"
                                >
                                    <x-widgets.select 
                                        id="countryId"
                                        wire="live=countryId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($countries)->map(function($country) use ($lang) {
                                            $translation = $country->countryTranslations->where('language_id', $lang?->id)->first();
                                            $countryName = $translation ? $translation->name : ($country->countryTranslations->first() ? $country->countryTranslations->first()->name : $country->iso_code_2);
                                            return ['value' => $country->id, 'label' => $countryName];
                                        })->toArray()]"
                                        error="countryId"
                                    />
                                </x-widgets.form-field>

                                {{-- 地区 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.zone_id')"
                                    labelFor="zoneId"
                                    error="zoneId"
                                >
                                    <x-widgets.select 
                                        id="zoneId"
                                        wire="zoneId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($zones)->map(fn($zoneName, $zoneId) => ['value' => $zoneId, 'label' => $zoneName])->toArray()]"
                                        error="zoneId"
                                        :disabled="empty($zones)"
                                    />
                                </x-widgets.form-field>

                                {{-- 详细地址1 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.address_1')"
                                    labelFor="address1"
                                    required
                                    error="address1"
                                    class="md:col-span-2"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="address1"
                                        wire="address1"
                                        error="address1"
                                    />
                                </x-widgets.form-field>

                                {{-- 详细地址2 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.address_2')"
                                    labelFor="address2"
                                    error="address2"
                                    class="md:col-span-2"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="address2"
                                        wire="address2"
                                        error="address2"
                                    />
                                </x-widgets.form-field>

                                {{-- 城市 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.city')"
                                    labelFor="city"
                                    required
                                    error="city"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="city"
                                        wire="city"
                                        error="city"
                                    />
                                </x-widgets.form-field>

                                {{-- 邮编 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.addresses.postcode')"
                                    labelFor="postcode"
                                    error="postcode"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="postcode"
                                        wire="postcode"
                                        error="postcode"
                                    />
                                </x-widgets.form-field>
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.addresses') }}" wire:navigate 
                                variant="secondary"
                            >
                                {{ __('app.cancel') }}
                            </x-widgets.button>
                            <x-widgets.button type="submit">
                                {{ __('app.save') }}
                            </x-widgets.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.addresses.label') }}" keywords="{{ __('manager.addresses.label') }}" />
