@php
    $isEdit = $languageId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('languages', $isEdit ? __('app.edit') : __('app.create'), __('manager.languages.label'), locaRoute('manager.languages'));
@endphp

<div class="min-h-[70vh] mb-10 ">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="languages" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.languages.label') }}
                    </h1>
                </div>


                <form wire:submit="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        {{-- 基本信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.language.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 语言代码 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.language.code')"
                                    labelFor="code"
                                    required
                                    error="code"
                                    :help="__('manager.language.code_helper')"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="code"
                                        wire="code"
                                        placeholder="例如: zh_CN"
                                        error="code"
                                    />
                                </x-widgets.form-field>

                                {{-- 语言名称 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.language.name')"
                                    labelFor="name"
                                    required
                                    error="name"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="name"
                                        wire="name"
                                        placeholder="例如: 简体中文"
                                        error="name"
                                    />
                                </x-widgets.form-field>

                                {{-- 是否默认 --}}
                                <div class="md:col-span-2">
                                    <x-widgets.checkbox 
                                        wire="live=default"
                                        :label="__('manager.language.is_default')"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.language.is_default_helper') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.languages') }}" wire:navigate 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.languages.label') }}" keywords="{{ __('manager.languages.label') }}" />
