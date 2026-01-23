@php
    $isEdit = $attributeId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('attributes', $isEdit ? __('app.edit') : __('app.create'), __('manager.attributes.label'), locaRoute('manager.attributes'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[80vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="attributes" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.attributes.label') }}
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.attribute.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 是否可筛选 --}}
                                <div>
                                    <x-widgets.checkbox 
                                        wire="isFilterable"
                                        :label="__('manager.attribute.is_filterable')"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.attribute.is_filterable_helper') }}</p>
                                </div>

                                {{-- 翻译状态 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.attribute.translation_status')"
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.attribute.translations') }}</h2>
                            <div class="space-y-4">
                                @foreach($languages as $language)
                                    <x-widgets.form-field 
                                        :label="__('manager.attribute.name') . ' (' . $language->name . ')'"
                                        :labelFor="'name_' . $language->id"
                                        :required="$language->default"
                                        :error="'translations.' . $language->id . '.name'"
                                        :help="$language->default ? __('manager.attribute.name_helper') : null"
                                    >
                                        <x-widgets.input 
                                            type="text" 
                                            id="name_{{ $language->id }}"
                                            wire="translations.{{ $language->id }}.name"
                                            placeholder="请输入属性名称"
                                            :error="'translations.' . $language->id . '.name'"
                                        />
                                    </x-widgets.form-field>
                                @endforeach
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.attributes') }}" wire:navigate 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.attributes.label') }}" keywords="{{ __('manager.attributes.label') }}" />
