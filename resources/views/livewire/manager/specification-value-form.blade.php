@php
    $isEdit = $specificationValueId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('specification-values', $isEdit ? __('app.edit') : __('app.create'), __('manager.specification_values.label'), locaRoute('manager.specification-values'));
@endphp

<div class="min-h-[70vh] mb-10 ">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />

        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="specification-values" />

            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.specification_values.label') }}
                    </h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-100 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                <form wire:submit.prevent="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-widgets.form-field 
                                :label="__('manager.specification_value.specification')"
                                error="specificationId"
                            >
                                <x-widgets.select 
                                    wire="specificationId"
                                    :options="[['value' => '', 'label' => __('app.please_select')], ...collect($specifications)->map(fn($spec) => ['value' => $spec->id, 'label' => $this->getSpecificationLabel($spec)])->toArray()]"
                                />
                            </x-widgets.form-field>

                            <x-widgets.form-field 
                                :label="__('manager.specification_value.translation_status')"
                                error="translationStatus"
                            >
                                <x-widgets.select 
                                    wire="translationStatus"
                                    :options="collect($translationStatusOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()->toArray()"
                                />
                            </x-widgets.form-field>
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold">
                                {{ __('manager.specification_value.translations') }}
                            </h2>

                            @php
                                $defaultLanguageId = $languages->firstWhere('default', true)?->id ?? $languages->first()?->id;
                            @endphp

                            <x-widgets.language-tabs :languages="$languages" :defaultId="$defaultLanguageId">
                                @foreach($languages as $language)
                                    <div
                                        data-teany-langpanel="{{ (int) $language->id }}"
                                        class="space-y-3 {{ (int) $language->id === (int) ($defaultLanguageId ?? 0) ? '' : 'hidden' }}"
                                    >
                                        <x-widgets.form-field 
                                            :label="__('manager.specification_value.name')"
                                            error="translations.{{ $language->id }}.name"
                                        >
                                            <x-widgets.input 
                                                wire="translations.{{ $language->id }}.name"
                                                error="translations.{{ $language->id }}.name"
                                            />
                                        </x-widgets.form-field>
                                    </div>
                                @endforeach
                            </x-widgets.language-tabs>
                        </div>

                        <div class="flex gap-2">
                            <x-widgets.button type="submit">
                                {{ __('app.save') }}
                            </x-widgets.button>
                            <x-widgets.button 
                                href="{{ locaRoute('manager.specification-values') }}" wire:navigate
                                variant="secondary"
                            >
                                {{ __('app.cancel') }}
                            </x-widgets.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ $specificationValueId ? __('app.edit') : __('app.create') }} {{ __('manager.specification_values.label') }}" keywords="{{ __('manager.specification_values.label') }}" />
