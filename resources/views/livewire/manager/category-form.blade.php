@php
    $isEdit = $categoryId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('categories', $isEdit ? __('app.edit') : __('app.create'), __('manager.categories.label'), locaRoute('manager.categories'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[80vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="categories" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.categories.label') }}
                    </h1>
                </div>

                <x-widgets.session-message type="message" />

                <form wire:submit="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        {{-- 基本信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.category.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- 图片上传 --}}
                                <div class="md:col-span-3">
                                    <x-widgets.file-upload 
                                        wire="image"
                                        accept="image/*"
                                        :preview="$imageUrl"
                                        previewSize="w-32 h-32"
                                        :label="__('manager.category.image')"
                                        error="image"
                                        :help="__('manager.category.image_helper')"
                                    />
                                </div>

                                {{-- 父分类 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.category.parent')"
                                    labelFor="parentId"
                                    error="parentId"
                                    :help="__('manager.category.parent_helper')"
                                >
                                    <x-widgets.select 
                                        id="parentId"
                                        wire="parentId"
                                        :options="[['value' => '', 'label' => __('manager.category.root')], ...collect($parentCategories)->map(function($parent) use ($lang) {
                                            $translation = $parent->categoryTranslations->where('language_id', $lang?->id)->first();
                                            $parentName = $translation ? $translation->name : ($parent->categoryTranslations->first() ? $parent->categoryTranslations->first()->name : $parent->slug);
                                            return ['value' => $parent->id, 'label' => $parentName . ' (' . $parent->id . ')'];
                                        })->toArray()]"
                                        error="parentId"
                                    />
                                </x-widgets.form-field>

                                {{-- URL别名 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.category.slug')"
                                    labelFor="slug"
                                    required
                                    error="slug"
                                    :help="__('manager.category.slug_helper')"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="slug"
                                        wire="slug"
                                        placeholder="例如: electronics"
                                        error="slug"
                                    />
                                </x-widgets.form-field>

                                {{-- 翻译状态 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.category.translation_status')"
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.category.translations') }}</h2>
                            <div class="space-y-4">
                                @foreach($languages as $language)
                                    <x-widgets.form-field 
                                        :label="__('manager.category.name') . ' (' . $language->name . ')'"
                                        :labelFor="'name_' . $language->id"
                                        :required="$language->default"
                                        :error="'translations.' . $language->id . '.name'"
                                        :help="$language->default ? __('manager.category.name_helper') : null"
                                    >
                                        <x-widgets.input 
                                            type="text" 
                                            id="name_{{ $language->id }}"
                                            wire="translations.{{ $language->id }}.name"
                                            placeholder="请输入分类名称"
                                            :error="'translations.' . $language->id . '.name'"
                                        />
                                    </x-widgets.form-field>
                                @endforeach
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.categories') }}" wire:navigate 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.categories.label') }}" keywords="{{ __('manager.categories.label') }}" />
