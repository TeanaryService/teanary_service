@php
    $isEdit = $warehouseId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('warehouses', $isEdit ? __('app.edit') : __('app.create'), __('manager.warehouses.label'), locaRoute('manager.warehouses'));
@endphp

<div class="min-h-[70vh] mb-10 ">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />

        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="warehouses" />

            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.warehouses.label') }}
                    </h1>
                </div>

                <form wire:submit="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.warehouse.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <x-widgets.form-field :label="__('manager.warehouse.name')" required error="name">
                                    <x-widgets.input type="text" wire="name" error="name" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('manager.warehouse.code')" required error="code" :help="__('manager.warehouse.code_helper')">
                                    <x-widgets.input type="text" wire="code" error="code" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.telephone')" error="telephone">
                                    <x-widgets.input type="text" wire="telephone" error="telephone" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('manager.warehouse.sort_order')" error="sortOrder">
                                    <x-widgets.input type="number" wire="sortOrder" min="0" error="sortOrder" />
                                </x-widgets.form-field>
                                <div class="md:col-span-2">
                                    <x-widgets.checkbox wire="live=active" :label="__('manager.warehouse.active')" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-widgets.checkbox wire="live=isDefault" :label="__('manager.language.is_default')" />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.warehouse.is_default_helper') }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.warehouse.shipping_origin') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <x-widgets.form-field :label="__('app.country')" error="countryId">
                                    <x-widgets.select
                                        wire="live=countryId"
                                        :options="array_merge([['value' => '', 'label' => __('app.please_select')]], collect($countries)->map(fn($c) => ['value' => $c['id'], 'label' => $c['name']])->toArray())"
                                        error="countryId"
                                    />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.zone')" error="zoneId">
                                    <x-widgets.select
                                        wire="zoneId"
                                        :options="array_merge([['value' => '', 'label' => __('app.please_select')]], collect($zones)->map(fn($z) => ['value' => $z['id'], 'label' => $z['name']])->toArray())"
                                        error="zoneId"
                                    />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.city')" error="city">
                                    <x-widgets.input type="text" wire="city" error="city" />
                                </x-widgets.form-field>
                                <x-widgets.form-field :label="__('app.postcode')" error="postcode">
                                    <x-widgets.input type="text" wire="postcode" error="postcode" />
                                </x-widgets.form-field>
                                <div class="md:col-span-2">
                                    <x-widgets.form-field :label="__('app.address_1')" error="address1">
                                        <x-widgets.input type="text" wire="address1" error="address1" />
                                    </x-widgets.form-field>
                                </div>
                                <div class="md:col-span-2">
                                    <x-widgets.form-field :label="__('app.address_2')" error="address2">
                                        <x-widgets.input type="text" wire="address2" error="address2" />
                                    </x-widgets.form-field>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button href="{{ locaRoute('manager.warehouses') }}" wire:navigate variant="secondary">
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.warehouses.label') }}" />
