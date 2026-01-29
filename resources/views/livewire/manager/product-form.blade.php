@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('products', $productId ? __('app.edit') . ' ' . __('manager.products.label') : __('app.create') . ' ' . __('manager.products.label'));
@endphp

<div class="min-h-[70vh] mb-10 ">
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
                {{-- 图片（多图） --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.products.images') }}
                        </h2>

                        <x-widgets.image-upload
                            :existing="$existingImages"
                            :upload="$newImages"
                            wire="newImages"
                            accept="image/*"
                            :multiple="true"
                            :label="__('app.upload_images') ?? '上传图片'"
                            error="newImages.*"
                            :help="__('app.image_upload_hint') ?? '支持多图上传，单张不超过 2MB。'"
                            removeExistingAction="removeProductImage"
                            removeExistingConfirm="{{ __('app.confirm_delete') }}"
                        />
                    </div>
                    
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
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 space-y-4">
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
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.products.attribute_values') }}
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($attributeList as $attribute)
                                @php
                                    $attrId = $attribute->id;
                                    $attrName = $attributeOptions[$attrId] ?? (string) $attrId;
                                    $isSelected = in_array($attrId, $selectedAttributes);
                                    $values = $attributeValueOptions[$attrId] ?? [];
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-4 space-y-3" wire:key="attribute-{{ $attrId }}">
                                    {{-- 属性复选框 --}}
                                    <div class="flex items-center">
                                        <x-widgets.checkbox 
                                            wire:model.live="selectedAttributes"
                                            :value="$attrId"
                                            :label="$attrName"
                                            class="!gap-2"
                                        />
                                    </div>
                                    
                                    {{-- 属性值单选（默认显示） --}}
                                    @if(!empty($values))
                                        <div class="ml-2 pl-4 border-l-2 border-gray-200 space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                {{ __('manager.products.attribute_value') }}
                                            </label>
                                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                                @foreach($values as $valueId => $valueName)
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input 
                                                            type="radio" 
                                                            wire:model.live="selectedAttributeValues.{{ $attrId }}"
                                                            value="{{ $valueId }}"
                                                            class="w-4 h-4 text-teal-600 border-gray-300 focus:ring-teal-500 focus:ring-2"
                                                        />
                                                        <span class="text-sm text-gray-700">{{ $valueName }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 多语言 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.products.translations') }}
                        </h2>

                        @php
                            $defaultLanguageId = $languages->firstWhere('default', true)?->id ?? $languages->first()?->id;
                        @endphp

                        <x-widgets.language-tabs :languages="$languages" :defaultId="$defaultLanguageId">
                            @foreach($languages as $language)
                                <div
                                    data-teany-langpanel="{{ (int) $language->id }}"
                                    class="space-y-3 {{ (int) $language->id === (int) ($defaultLanguageId ?? 0) ? '' : 'hidden' }}"
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
                                        <x-widgets.quill-editor
                                            id="product_description_{{ $language->id }}"
                                            wire="defer=translations.{{ $language->id }}.description"
                                            minHeight="280px"
                                        >{!! $translations[$language->id]['description'] ?? '' !!}</x-widgets.quill-editor>
                                    </x-widgets.form-field>
                                </div>
                            @endforeach
                        </x-widgets.language-tabs>
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