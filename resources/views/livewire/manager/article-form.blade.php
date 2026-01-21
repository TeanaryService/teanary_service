@php
    $isEdit = $articleId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('articles', $isEdit ? __('app.edit') : __('app.create'), __('manager.articles.label'), locaRoute('manager.articles'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="articles" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.articles.label') }}
                    </h1>
                </div>

                <x-widgets.session-message type="message" />

                <form wire:submit="save">
                    <x-widgets.card>
                        <div class="space-y-6">
                            {{-- 基本信息 --}}
                            <x-widgets.section :title="__('manager.article.basic_info')">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- 图片上传 --}}
                                <div class="md:col-span-3">
                                    <x-widgets.file-upload 
                                        wire="image"
                                        accept="image/*"
                                        :preview="$imageUrl"
                                        previewSize="w-32 h-32"
                                        :label="__('manager.article.image')"
                                        error="image"
                                        :help="__('manager.article.image_helper')"
                                    />
                                </div>

                                {{-- URL别名 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.article.slug')"
                                    labelFor="slug"
                                    required
                                    error="slug"
                                    :help="__('manager.article.slug_helper')"
                                    class="md:col-span-2"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="slug"
                                        wire="slug"
                                        placeholder="例如: my-article-title"
                                        error="slug"
                                    />
                                </x-widgets.form-field>

                                {{-- 用户 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.article.user_id')"
                                    labelFor="userId"
                                    error="userId"
                                    :help="__('manager.article.user_id_helper')"
                                >
                                    <x-widgets.select 
                                        id="userId"
                                        wire="userId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($users)->map(fn($user) => ['value' => $user->id, 'label' => $user->name])->toArray()]"
                                        error="userId"
                                    />
                                </x-widgets.form-field>

                                {{-- 发布状态 --}}
                                <x-widgets.checkbox 
                                    wire="isPublished"
                                    :label="__('manager.article.is_published')"
                                />

                                {{-- 翻译状态 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.article.translation_status')"
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.article.translations') }}</h2>
                            <div class="space-y-6">
                                @foreach($languages as $language)
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <h3 class="text-md font-semibold text-gray-900 mb-4">{{ $language->name }}</h3>
                                        <div class="space-y-4">
                                            {{-- 标题 --}}
                                            <x-widgets.form-field 
                                                :label="__('manager.article.title')"
                                                :labelFor="'title_' . $language->id"
                                                :required="$language->default"
                                                :error="'translations.' . $language->id . '.title'"
                                            >
                                                <x-widgets.input 
                                                    type="text" 
                                                    id="title_{{ $language->id }}"
                                                    wire="translations.{{ $language->id }}.title"
                                                    placeholder="请输入文章标题"
                                                    :error="'translations.' . $language->id . '.title'"
                                                />
                                            </x-widgets.form-field>

                                            {{-- 摘要 --}}
                                            <x-widgets.form-field 
                                                :label="__('manager.article.summary')"
                                                :labelFor="'summary_' . $language->id"
                                                :required="$language->default"
                                                :error="'translations.' . $language->id . '.summary'"
                                                :help="$language->default ? __('manager.article.summary_helper') : null"
                                            >
                                                <x-widgets.textarea 
                                                    id="summary_{{ $language->id }}"
                                                    wire="translations.{{ $language->id }}.summary"
                                                    rows="3"
                                                    maxlength="500"
                                                    placeholder="请输入文章摘要"
                                                    :error="'translations.' . $language->id . '.summary'"
                                                />
                                            </x-widgets.form-field>

                                            {{-- 内容 --}}
                                            <x-widgets.form-field 
                                                :label="__('manager.article.content')"
                                                :labelFor="'content_' . $language->id"
                                                :error="'translations.' . $language->id . '.content'"
                                                help="支持HTML标签：&lt;p&gt;, &lt;h1&gt;-&lt;h6&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;img&gt;"
                                            >
                                                <x-widgets.textarea 
                                                    id="content_{{ $language->id }}"
                                                    wire="translations.{{ $language->id }}.content"
                                                    rows="10"
                                                    class="font-mono text-sm"
                                                    placeholder="请输入文章内容（支持HTML）"
                                                    :error="'translations.' . $language->id . '.content'"
                                                />
                                            </x-widgets.form-field>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.articles') }}" 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.articles.label') }}" keywords="{{ __('manager.articles.label') }}" />
