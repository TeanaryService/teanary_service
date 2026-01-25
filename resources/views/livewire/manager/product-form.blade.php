@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('products', $productId ? __('app.edit') . ' ' . __('manager.products.label') : __('app.create') . ' ' . __('manager.products.label'));
@endphp

<div class="min-h-[70vh] mb-10 bg-teal-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="products" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $productId ? __('app.edit') : __('app.create') }}
                        {{ __('manager.products.label') }}
                    </h1>
                </div>


                <form wire:submit.prevent="save" class="space-y-6">
                    {{-- 基本信息 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-widgets.form-field :label="__('manager.products.slug')" error="slug">
                                <x-widgets.input type="text" wire="slug" error="slug" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('manager.products.source_url')" error="sourceUrl">
                                <x-widgets.input type="text" wire="sourceUrl" error="sourceUrl" />
                            </x-widgets.form-field>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-widgets.form-field :label="__('manager.products.status')" error="status">
                                <x-widgets.select wire="status" :options="$statusOptions" error="status" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('manager.products.translation_status')" error="translationStatus">
                                <x-widgets.select wire="translationStatus" :options="$translationStatusOptions" error="translationStatus" />
                            </x-widgets.form-field>
                        </div>
                    </div>

                    {{-- 变体管理（笛卡尔积 SKU） --}}
                    @if($productId)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                            @livewire(\App\Livewire\Manager\Components\ManageProductVariants::class, ['productId' => $productId], key('manage-product-variants-' . $productId))
                        </div>
                    @endif

                    {{-- 分类 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.products.categories') }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach($categories as $cat)
                                <x-widgets.checkbox 
                                    wire="categoryIds"
                                    :value="$cat['id']"
                                    :label="$cat['name']"
                                    class="!gap-2"
                                />
                            @endforeach
                        </div>
                    </div>

                    {{-- 属性 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('manager.products.attribute_values') }}
                            </h2>
                            <x-widgets.button 
                                type="button"
                                wire:click="addAttributeValueRow"
                                variant="secondary"
                                size="sm"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('app.add') }}
                            </x-widgets.button>
                        </div>

                        <div class="space-y-3">
                            @foreach($attributeValues as $index => $row)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('manager.products.attribute') }}
                                        </label>
                                        <x-widgets.select 
                                            wire="attributeValues.{{ $index }}.attribute_id"
                                            :options="[['value' => '', 'label' => __('app.please_select')], ...collect($attributeOptions)->map(fn($name, $id) => ['value' => $id, 'label' => $name])->toArray()]"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('manager.products.attribute_value') }}
                                        </label>
                                        @php
                                            $attrId = $row['attribute_id'] ?? null;
                                            $values = $attrId && isset($attributeValueOptions[$attrId]) ? $attributeValueOptions[$attrId] : [];
                                        @endphp
                                        <x-widgets.select 
                                            wire="attributeValues.{{ $index }}.attribute_value_id"
                                            :options="[['value' => '', 'label' => __('app.please_select')], ...collect($values)->map(fn($vname, $vid) => ['value' => $vid, 'label' => $vname])->toArray()]"
                                        />
                                    </div>
                                    <div class="flex items-center md:justify-end">
                                        <x-widgets.button 
                                            type="button"
                                            wire:click="removeAttributeValueRow({{ $index }})"
                                            variant="danger-outline"
                                            size="sm"
                                        >
                                            {{ __('app.delete') }}
                                        </x-widgets.button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 图片（多图） --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.products.images') }}
                        </h2>

                        @if(!empty($existingImages))
                            <div class="flex flex-wrap gap-3 mb-4">
                                @foreach($existingImages as $img)
                                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                                        <img src="{{ $img->getUrl('thumb') }}" alt="" class="w-full h-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <x-widgets.file-upload 
                            wire="newImages"
                            accept="image/*"
                            :multiple="false"
                            :label="__('app.upload_images') ?? '上传图片'"
                            error="newImages.*"
                            :help="__('app.image_upload_hint') ?? '支持多图上传，单张不超过 2MB。'"
                            :showPreview="false"
                        />
                    </div>

                    {{-- 多语言 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.products.translations') }}
                        </h2>

                        @php
                            $defaultLanguageId = $languages->first()?->id;
                        @endphp

                        <div
                            x-data="{ activeLang: {{ (int) ($defaultLanguageId ?? 0) }} }"
                            class="space-y-4"
                        >
                            {{-- Tabs --}}
                            <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
                                @foreach($languages as $language)
                                    <x-widgets.button
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        class="!rounded-lg !shadow-none hover:!shadow-none"
                                        x-bind:class="activeLang === {{ (int) $language->id }}
                                            ? '!bg-teal-600 text-white border-teal-600 hover:bg-teal-700 hover:border-teal-700'
                                            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:border-gray-300'"
                                        x-on:click="activeLang = {{ (int) $language->id }}"
                                    >
                                        {{ $language->name }}
                                    </x-widgets.button>
                                @endforeach
                            </div>

                            {{-- Panels --}}
                            <div class="space-y-4">
                                @foreach($languages as $language)
                                    <div
                                        x-show="activeLang === {{ (int) $language->id }}"
                                        x-cloak
                                        class="space-y-3"
                                        wire:key="product-translation-tab-{{ $language->id }}"
                                    >
                                        <x-widgets.form-field :label="__('manager.products.name')" :error="'translations.' . $language->id . '.name'">
                                            <x-widgets.input
                                                type="text"
                                                wire="translations.{{ $language->id }}.name"
                                                :error="'translations.' . $language->id . '.name'"
                                            />
                                        </x-widgets.form-field>

                                        <x-widgets.form-field :label="__('manager.products.short_description')">
                                            <x-widgets.textarea
                                                wire="translations.{{ $language->id }}.short_description"
                                                rows="2"
                                            />
                                        </x-widgets.form-field>

                                        <x-widgets.form-field :label="__('manager.products.description')">
                                            <x-widgets.textarea
                                                wire="translations.{{ $language->id }}.description"
                                                rows="4"
                                            />
                                        </x-widgets.form-field>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <x-widgets.button type="submit">
                            {{ __('app.save') }}
                        </x-widgets.button>
                        <x-widgets.button 
                            href="{{ locaRoute('manager.products') }}" wire:navigate
                            variant="secondary"
                        >
                            {{ __('app.cancel') }}
                        </x-widgets.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ $productId ? __('app.edit') : __('app.create') }} {{ __('manager.products.label') }}" keywords="{{ __('manager.products.label') }}" />