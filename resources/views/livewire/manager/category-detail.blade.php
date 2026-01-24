@php
    $parentCategories = $this->parentCategories;
    $languages = $this->languages;
    $translationStatusOptions = $this->translationStatusOptions;
    $isEdit = $this->category !== null;
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') . ': ' . ($this->category->categoryTranslations->first()?->name ?? $this->category->slug) : __('app.add_new') . ' ' . __('manager.categories.label') }}
                    </h1>
                </div>
                <a href="{{ locaRoute('manager.categories') }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    {{ __('app.back') }}
                </a>
            </div>

            @if(session('message'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('message') }}
                </div>
            @endif

            <form wire:submit="save" class="space-y-6">
                {{-- 基本信息 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.category.basic_info') }}</h3>
                    
                    <div class="space-y-4">
                        {{-- 图片 --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.category.image') }}</label>
                            <div class="flex items-center gap-4">
                                @if($isEdit && $this->category->getFirstMediaUrl('image', 'thumb'))
                                    <img src="{{ $this->category->getFirstMediaUrl('image', 'thumb') }}" alt="Category Image" 
                                        class="w-24 h-24 rounded-lg object-cover border-2 border-gray-200">
                                @else
                                    <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center border-2 border-gray-200">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <input type="file" wire:model="image" accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                    <p class="mt-1 text-xs text-gray-500">{{ __('manager.category.image_helper') }}</p>
                                    @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.category.slug') }} <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="slug" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-gray-500">{{ __('manager.category.slug_helper') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.category.parent') }}</label>
                                <select wire:model="parentId" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <option value="">{{ __('manager.category.root') }}</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->display_name }}</option>
                                    @endforeach
                                </select>
                                @error('parentId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-gray-500">{{ __('manager.category.parent_helper') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.category.translation_status') }} <span class="text-red-500">*</span></label>
                                <select wire:model="translationStatus" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @foreach($translationStatusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('translationStatus') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 多语言翻译 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.category.translations') }}</h3>
                    
                    <div class="space-y-4">
                        @foreach($languages as $language)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">
                                    {{ $language->name }}
                                    @if($language->is_default)
                                        <span class="text-xs text-gray-500">({{ __('app.default') }})</span>
                                    @endif
                                </h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('manager.category.name') }}
                                            @if($language->is_default)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        <input type="text" wire:model="translations.{{ $language->id }}.name" 
                                            @if($language->is_default) required @endif
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        @error('translations.' . $language->id . '.name') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('app.description') }}
                                        </label>
                                        <textarea wire:model="translations.{{ $language->id }}.description" rows="3"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 提交按钮 --}}
                <div class="flex justify-end">
                    <button type="submit" 
                        class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                        {{ __('app.save') }}
                    </button>
                </div>
            </form>
        </div>
    </x-manager.layout>
</div>
