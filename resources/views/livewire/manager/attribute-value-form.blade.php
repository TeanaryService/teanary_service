@php
    $isEdit = $attributeValueId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('attribute-values', $isEdit ? __('app.edit') : __('app.create'), __('manager.attribute_values.label'), locaRoute('manager.attribute-values'));
@endphp

<div class="min-h-[70vh] mb-10 ">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="attribute-values" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.attribute_values.label') }}
                    </h1>
                </div>


                <form wire:submit="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        {{-- 基本信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.attribute_value.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 属性 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.attribute_value.attribute')"
                                    labelFor="attributeId"
                                    required
                                    error="attributeId"
                                    :help="__('manager.attribute_value.attribute_helper')"
                                >
                                    <x-widgets.select 
                                        id="attributeId"
                                        wire="attributeId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($attributeModels)->map(function($attribute) use ($lang) {
                                            $translation = $attribute->attributeTranslations->where('language_id', $lang?->id)->first();
                                            $attributeName = $translation ? $translation->name : ($attribute->attributeTranslations->first() ? $attribute->attributeTranslations->first()->name : $attribute->id);
                                            return ['value' => $attribute->id, 'label' => $attributeName];
                                        })->toArray()]"
                                        error="attributeId"
                                    />
                                </x-widgets.form-field>

                                {{-- 翻译状态 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.attribute_value.translation_status')"
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.attribute_value.translations') }}</h2>
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
                                            :label="__('manager.attribute_value.name')"
                                            :labelFor="'name_' . $language->id"
                                            :required="$language->default"
                                            :error="'translations.' . $language->id . '.name'"
                                            :help="$language->default ? __('manager.attribute_value.name_helper') : null"
                                        >
                                            <x-widgets.input 
                                                type="text" 
                                                id="name_{{ $language->id }}"
                                                wire="translations.{{ $language->id }}.name"
                                                placeholder="请输入属性值名称"
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
                                href="{{ locaRoute('manager.attribute-values') }}" wire:navigate 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.attribute_values.label') }}" keywords="{{ __('manager.attribute_values.label') }}" />
