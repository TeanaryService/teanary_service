@php
    $isEdit = $attributeId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('attributes', $isEdit ? __('app.edit') : __('app.create'), __('manager.attributes.label'), locaRoute('manager.attributes'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="attributes" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.attributes.label') }}
                    </h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
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
                                    <label class="flex items-center gap-3">
                                        <input 
                                            type="checkbox" 
                                            wire:model="isFilterable"
                                            class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                        />
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ __('manager.attribute.is_filterable') }}
                                        </span>
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.attribute.is_filterable_helper') }}</p>
                                </div>

                                {{-- 翻译状态 --}}
                                <div>
                                    <label for="translationStatus" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.attribute.translation_status') }} <span class="text-red-500">*</span>
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.attribute.translations') }}</h2>
                            <div class="space-y-4">
                                @foreach($languages as $language)
                                    <div>
                                        <label for="name_{{ $language->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('manager.attribute.name') }} ({{ $language->name }})
                                            @if($language->default)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        <input 
                                            type="text" 
                                            id="name_{{ $language->id }}"
                                            wire:model="translations.{{ $language->id }}.name"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('translations.' . $language->id . '.name') border-red-300 @enderror"
                                            placeholder="请输入属性名称"
                                        />
                                        @error('translations.' . $language->id . '.name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        @if($language->default)
                                            <p class="mt-1 text-xs text-gray-500">{{ __('manager.attribute.name_helper') }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <a href="{{ locaRoute('manager.attributes') }}" 
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

@pushOnce('seo')
    <x-layouts.seo title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.attributes.label') }}" description="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.attributes.label') }}"
        keywords="{{ __('manager.attributes.label') }}" />
@endPushOnce
