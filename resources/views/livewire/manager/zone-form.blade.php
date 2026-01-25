@php
    $isEdit = $zoneId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('zones', $isEdit ? __('app.edit') : __('app.create'), __('manager.zones.label'), locaRoute('manager.zones'));
@endphp

<div class="min-h-[70vh] mb-10 ">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="zones" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.zones.label') }}
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.zone.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 国家 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.zone.country')"
                                    labelFor="countryId"
                                    required
                                    error="countryId"
                                    :help="__('manager.zone.country_helper')"
                                >
                                    <x-widgets.select 
                                        id="countryId"
                                        wire="countryId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($countries)->map(function($country) use ($lang) {
                                            $translation = $country->countryTranslations->where('language_id', $lang?->id)->first();
                                            $countryName = $translation ? $translation->name : ($country->countryTranslations->first() ? $country->countryTranslations->first()->name : $country->iso_code_2);
                                            return ['value' => $country->id, 'label' => $countryName];
                                        })->toArray()]"
                                        error="countryId"
                                    />
                                </x-widgets.form-field>

                                {{-- 地区代码 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.zone.code')"
                                    labelFor="code"
                                    error="code"
                                    :help="__('manager.zone.code_helper')"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="code"
                                        wire="code"
                                        placeholder="例如: BJ"
                                        error="code"
                                    />
                                </x-widgets.form-field>

                                {{-- 激活状态 --}}
                                <x-widgets.checkbox 
                                    wire="active"
                                    :label="__('manager.zone.active')"
                                />

                                {{-- 翻译状态 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.zone.translation_status')"
                                    labelFor="translationStatus"
                                    required
                                    error="translationStatus"
                                >
                                    <x-widgets.select 
                                        id="translationStatus"
                                        wire="translationStatus"
                                        :options="$translationStatusOptions"
                                        error="translationStatus"
                                    />
                                </x-widgets.form-field>
                            </div>
                        </div>

                        {{-- 翻译 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.zone.translations') }}</h2>
                            @php
                                $defaultLanguageId = $languages->firstWhere('default', true)?->id ?? $languages->first()?->id;
                            @endphp

                            <x-widgets.language-tabs :languages="$languages" :defaultId="$defaultLanguageId">
                                @foreach($languages as $language)
                                    <div
                                        data-teany-langpanel="{{ (int) $language->id }}"
                                        class="space-y-3 {{ (int) $language->id === (int) ($defaultLanguageId ?? 0) ? '' : 'hidden' }}"
                                    >
                                        <x-widgets.form-field 
                                            :label="__('manager.zone.name')"
                                            :labelFor="'translation_' . $language->id"
                                            :required="$language->default"
                                            :error="'translations.' . $language->id . '.name'"
                                            :help="$language->default ? __('manager.zone.name_helper') : null"
                                        >
                                            <x-widgets.input 
                                                type="text" 
                                                id="translation_{{ $language->id }}"
                                                wire="translations.{{ $language->id }}.name"
                                                placeholder="请输入地区名称"
                                                :error="'translations.' . $language->id . '.name'"
                                            />
                                        </x-widgets.form-field>
                                    </div>
                                @endforeach
                            </x-widgets.language-tabs>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.zones') }}" wire:navigate 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.zones.label') }}" keywords="{{ __('manager.zones.label') }}" />
