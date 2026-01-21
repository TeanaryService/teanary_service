@php
    $isEdit = $languageId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('languages', $isEdit ? __('app.edit') : __('app.create'), __('manager.languages.label'), locaRoute('manager.languages'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="languages" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.languages.label') }}
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.language.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 语言代码 --}}
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.language.code') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="code"
                                        wire:model="code"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('code') border-red-300 @enderror"
                                        placeholder="例如: zh_CN"
                                    />
                                    @error('code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.language.code_helper') }}</p>
                                </div>

                                {{-- 语言名称 --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.language.name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="name"
                                        wire:model="name"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('name') border-red-300 @enderror"
                                        placeholder="例如: 简体中文"
                                    />
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 是否默认 --}}
                                <div class="md:col-span-2">
                                    <label class="flex items-center gap-3">
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="default"
                                            class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                        />
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ __('manager.language.is_default') }}
                                        </span>
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.language.is_default_helper') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <a href="{{ locaRoute('manager.languages') }}" 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.languages.label') }}" keywords="{{ __('manager.languages.label') }}" />
