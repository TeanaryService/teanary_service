@php
    $isEdit = $categoryId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('categories', $isEdit ? __('app.edit') : __('app.create'), __('manager.categories.label'), locaRoute('manager.categories'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="categories" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.categories.label') }}
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.category.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- 图片上传 --}}
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.category.image') }} <span class="text-red-500">*</span>
                                    </label>
                                    @if($imageUrl)
                                        <div class="mb-4">
                                            <img src="{{ $imageUrl }}" alt="Current image" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                                        </div>
                                    @endif
                                    <input 
                                        type="file" 
                                        wire:model="image"
                                        accept="image/*"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('image') border-red-300 @enderror"
                                    />
                                    @error('image')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @if($image)
                                        <p class="mt-1 text-xs text-gray-500">已选择: {{ $image->getClientOriginalName() }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.category.image_helper') }}</p>
                                </div>

                                {{-- 父分类 --}}
                                <div>
                                    <label for="parentId" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.category.parent') }}
                                    </label>
                                    <select 
                                        id="parentId"
                                        wire:model="parentId"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('parentId') border-red-300 @enderror"
                                    >
                                        <option value="">{{ __('manager.category.root') }}</option>
                                        @foreach($parentCategories as $parent)
                                            <option value="{{ $parent->id }}">
                                                @php
                                                    $translation = $parent->categoryTranslations->where('language_id', $lang?->id)->first();
                                                    $parentName = $translation ? $translation->name : ($parent->categoryTranslations->first() ? $parent->categoryTranslations->first()->name : $parent->slug);
                                                @endphp
                                                {{ $parentName }} ({{ $parent->id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parentId')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.category.parent_helper') }}</p>
                                </div>

                                {{-- URL别名 --}}
                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.category.slug') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="slug"
                                        wire:model="slug"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('slug') border-red-300 @enderror"
                                        placeholder="例如: electronics"
                                    />
                                    @error('slug')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.category.slug_helper') }}</p>
                                </div>

                                {{-- 翻译状态 --}}
                                <div>
                                    <label for="translationStatus" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.category.translation_status') }} <span class="text-red-500">*</span>
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.category.translations') }}</h2>
                            <div class="space-y-4">
                                @foreach($languages as $language)
                                    <div>
                                        <label for="name_{{ $language->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('manager.category.name') }} ({{ $language->name }})
                                            @if($language->default)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        <input 
                                            type="text" 
                                            id="name_{{ $language->id }}"
                                            wire:model="translations.{{ $language->id }}.name"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('translations.' . $language->id . '.name') border-red-300 @enderror"
                                            placeholder="请输入分类名称"
                                        />
                                        @error('translations.' . $language->id . '.name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        @if($language->default)
                                            <p class="mt-1 text-xs text-gray-500">{{ __('manager.category.name_helper') }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <a href="{{ locaRoute('manager.categories') }}" 
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
