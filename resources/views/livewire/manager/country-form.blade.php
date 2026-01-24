@php
    $isEdit = $countryId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('countries', $isEdit ? __('app.edit') : __('app.create'), __('manager.countries.label'), locaRoute('manager.countries'));
@endphp

<div class="min-h-[70vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="countries" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.countries.label') }}
                    </h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-100 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                <form wire:submit="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        {{-- 基本信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.country.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- ISO代码2 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.country.iso_code_2')"
                                    labelFor="isoCode2"
                                    error="isoCode2"
                                    :help="__('manager.country.iso_code_2_helper')"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="isoCode2"
                                        wire="isoCode2"
                                        maxlength="2"
                                        placeholder="例如: CN"
                                        error="isoCode2"
                                    />
                                </x-widgets.form-field>

                                {{-- ISO代码3 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.country.iso_code_3')"
                                    labelFor="isoCode3"
                                    error="isoCode3"
                                    :help="__('manager.country.iso_code_3_helper')"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="isoCode3"
                                        wire="isoCode3"
                                        maxlength="3"
                                        placeholder="例如: CHN"
                                        error="isoCode3"
                                    />
                                </x-widgets.form-field>

                                {{-- 邮编必填 --}}
                                <x-widgets.checkbox 
                                    wire="postcodeRequired"
                                    :label="__('manager.country.postcode_required')"
                                />

                                {{-- 激活状态 --}}
                                <x-widgets.checkbox 
                                    wire="active"
                                    :label="__('manager.country.active')"
                                />

                                {{-- 翻译状态 --}}
                                <div>
                                    <label for="translationStatus" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.country.translation_status') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        id="translationStatus"
                                        wire:model="translationStatus"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('translationStatus') border-red-300 @enderror"
                                    >
                                        @foreach($translationStatusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('translationStatus')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 翻译 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.country.translations') }}</h2>
                            <div class="space-y-4">
                                @foreach($languages as $language)
                                    <x-widgets.form-field 
                                        :label="__('manager.country.name') . ' (' . $language->name . ')'"
                                        :labelFor="'translation_' . $language->id"
                                        :required="$language->default"
                                        :error="'translations.' . $language->id . '.name'"
                                        :help="$language->default ? __('manager.country.name_helper') : null"
                                    >
                                        <x-widgets.input 
                                            type="text" 
                                            id="translation_{{ $language->id }}"
                                            wire="translations.{{ $language->id }}.name"
                                            placeholder="请输入国家名称"
                                            :error="'translations.' . $language->id . '.name'"
                                        />
                                    </x-widgets.form-field>
                                @endforeach
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.countries') }}" wire:navigate 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.countries.label') }}" keywords="{{ __('manager.countries.label') }}" />
