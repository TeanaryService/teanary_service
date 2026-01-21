@php
    $isEdit = $articleId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('articles', $isEdit ? __('app.edit') : __('app.create'), __('filament.ArticleResource.label'), locaRoute('manager.articles'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="articles" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('filament.ArticleResource.label') }}
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.article.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- 图片上传 --}}
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('filament.article.image') }} <span class="text-red-500">*</span>
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
                                    <p class="mt-1 text-xs text-gray-500">{{ __('filament.article.image_helper') }}</p>
                                </div>

                                {{-- URL别名 --}}
                                <div class="md:col-span-2">
                                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('filament.article.slug') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="slug"
                                        wire:model="slug"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('slug') border-red-300 @enderror"
                                        placeholder="例如: my-article-title"
                                    />
                                    @error('slug')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">{{ __('filament.article.slug_helper') }}</p>
                                </div>

                                {{-- 用户 --}}
                                <div>
                                    <label for="userId" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('filament.article.user_id') }}
                                    </label>
                                    <select 
                                        id="userId"
                                        wire:model="userId"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('userId') border-red-300 @enderror"
                                    >
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('userId')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">{{ __('filament.article.user_id_helper') }}</p>
                                </div>

                                {{-- 发布状态 --}}
                                <div>
                                    <label class="flex items-center gap-3">
                                        <input 
                                            type="checkbox" 
                                            wire:model="isPublished"
                                            class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                        />
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ __('filament.article.is_published') }}
                                        </span>
                                    </label>
                                </div>

                                {{-- 翻译状态 --}}
                                <div>
                                    <label for="translationStatus" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('filament.article.translation_status') }} <span class="text-red-500">*</span>
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('filament.article.translations') }}</h2>
                            <div class="space-y-6">
                                @foreach($languages as $language)
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <h3 class="text-md font-semibold text-gray-900 mb-4">{{ $language->name }}</h3>
                                        <div class="space-y-4">
                                            {{-- 标题 --}}
                                            <div>
                                                <label for="title_{{ $language->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ __('filament.article.title') }}
                                                    @if($language->default)
                                                        <span class="text-red-500">*</span>
                                                    @endif
                                                </label>
                                                <input 
                                                    type="text" 
                                                    id="title_{{ $language->id }}"
                                                    wire:model="translations.{{ $language->id }}.title"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('translations.' . $language->id . '.title') border-red-300 @enderror"
                                                    placeholder="请输入文章标题"
                                                />
                                                @error('translations.' . $language->id . '.title')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- 摘要 --}}
                                            <div>
                                                <label for="summary_{{ $language->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ __('filament.article.summary') }}
                                                    @if($language->default)
                                                        <span class="text-red-500">*</span>
                                                    @endif
                                                </label>
                                                <textarea 
                                                    id="summary_{{ $language->id }}"
                                                    wire:model="translations.{{ $language->id }}.summary"
                                                    rows="3"
                                                    maxlength="500"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('translations.' . $language->id . '.summary') border-red-300 @enderror"
                                                    placeholder="请输入文章摘要"
                                                ></textarea>
                                                @error('translations.' . $language->id . '.summary')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                                @if($language->default)
                                                    <p class="mt-1 text-xs text-gray-500">{{ __('filament.article.summary_helper') }}</p>
                                                @endif
                                            </div>

                                            {{-- 内容 --}}
                                            <div>
                                                <label for="content_{{ $language->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ __('filament.article.content') }}
                                                </label>
                                                <textarea 
                                                    id="content_{{ $language->id }}"
                                                    wire:model="translations.{{ $language->id }}.content"
                                                    rows="10"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('translations.' . $language->id . '.content') border-red-300 @enderror font-mono text-sm"
                                                    placeholder="请输入文章内容（支持HTML）"
                                                ></textarea>
                                                @error('translations.' . $language->id . '.content')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                                <p class="mt-1 text-xs text-gray-500">支持HTML标签：&lt;p&gt;, &lt;h1&gt;-&lt;h6&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;img&gt;</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <a href="{{ locaRoute('manager.articles') }}" 
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
