@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('products', $productId ? __('app.edit') . ' ' . __('manager.products.label') : __('app.create') . ' ' . __('manager.products.label'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="products" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $productId ? __('app.edit') : __('app.create') }}
                        {{ __('manager.products.label') }}
                    </h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                <form wire:submit.prevent="save" class="space-y-6">
                    {{-- 基本信息 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('manager.products.slug') }}
                                </label>
                                <input type="text" wire:model="slug"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @error('slug') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('manager.products.source_url') }}
                                </label>
                                <input type="text" wire:model="sourceUrl"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @error('sourceUrl') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('manager.products.status') }}
                                </label>
                                <select wire:model="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('manager.products.translation_status') }}
                                </label>
                                <select wire:model="translationStatus" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @foreach($translationStatusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('translationStatus') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
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
                                <label class="inline-flex items-center text-sm text-gray-700">
                                    <input type="checkbox" wire:model="categoryIds" value="{{ $cat['id'] }}"
                                           class="rounded border-gray-300 text-teal-600 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <span class="ml-2">{{ $cat['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 属性 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('manager.products.attribute_values') }}
                            </h2>
                            <button type="button"
                                    wire:click="addAttributeValueRow"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('app.add') }}
                            </button>
                        </div>

                        <div class="space-y-3">
                            @foreach($attributeValues as $index => $row)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('manager.products.attribute') }}
                                        </label>
                                        <select wire:model="attributeValues.{{ $index }}.attribute_id"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                            <option value="">{{ __('app.please_select') }}</option>
                                            @foreach($attributeOptions as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('manager.products.attribute_value') }}
                                        </label>
                                        @php
                                            $attrId = $row['attribute_id'] ?? null;
                                            $values = $attrId && isset($attributeValueOptions[$attrId]) ? $attributeValueOptions[$attrId] : [];
                                        @endphp
                                        <select wire:model="attributeValues.{{ $index }}.attribute_value_id"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                            <option value="">{{ __('app.please_select') }}</option>
                                            @foreach($values as $vid => $vname)
                                                <option value="{{ $vid }}">{{ $vname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center md:justify-end">
                                        <button type="button"
                                                wire:click="removeAttributeValueRow({{ $index }})"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                                            {{ __('app.delete') }}
                                        </button>
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

                        <div>
                            <input type="file" wire:model="newImages" multiple
                                   class="w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                            @error('newImages.*') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                {{ __('app.image_upload_hint') ?? '支持多图上传，单张不超过 2MB。' }}
                            </p>
                        </div>
                    </div>

                    {{-- 多语言 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.products.translations') }}
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($languages as $language)
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('manager.products.name') }} ({{ $language->name }})
                                        </label>
                                        <input type="text"
                                               wire:model="translations.{{ $language->id }}.name"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        @error('translations.' . $language->id . '.name')
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('manager.products.short_description') }} ({{ $language->name }})
                                        </label>
                                        <textarea
                                            wire:model="translations.{{ $language->id }}.short_description"
                                            rows="2"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                        ></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('manager.products.description') }} ({{ $language->name }})
                                        </label>
                                        <textarea
                                            wire:model="translations.{{ $language->id }}.description"
                                            rows="4"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                        ></textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors">
                            {{ __('app.save') }}
                        </button>
                        <a href="{{ locaRoute('manager.products') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            {{ __('app.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

